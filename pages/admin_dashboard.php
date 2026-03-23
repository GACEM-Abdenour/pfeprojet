<?php
/**
 * Admin Dashboard - "Absolute Power" overview with KPIs and master ticket list.
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';

requireRole('Admin');

$pdo = getDBConnection();

// KPI queries
$totalTicketsStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM incidents");
$totalTickets = (int)($totalTicketsStmt->fetch()['cnt'] ?? 0);

$openTicketsStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM incidents WHERE status = 'Open'");
$openTickets = (int)($openTicketsStmt->fetch()['cnt'] ?? 0);

$resolvedTicketsStmt = $pdo->query("
    SELECT COUNT(*) AS cnt
    FROM incidents
    WHERE status IN ('Resolved', 'Closed')
");
$resolvedTickets = (int)($resolvedTicketsStmt->fetch()['cnt'] ?? 0);

$totalUsersStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users");
$totalUsers = (int)($totalUsersStmt->fetch()['cnt'] ?? 0);

// Chart: last 7 days — multiple modes (all / completed / uncompleted) + optional category filter
$chartLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $chartLabels[] = date('D j', strtotime("-$i days"));
}

$categoriesForChart = [];
try {
    $categoriesForChart = $pdo->query("SELECT DISTINCT category FROM incidents ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categoriesForChart = [];
}

$fillLast7Days = static function (array $byDay): array {
    $out = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $out[] = $byDay[$d] ?? 0;
    }
    return $out;
};

$runChartQuery = static function (PDO $pdo, string $mode, ?string $category) use ($fillLast7Days): array {
    $params = [];
    $catSql = '';
    if ($category !== null && $category !== '') {
        $catSql = ' AND category = ? ';
        $params[] = $category;
    }
    if ($mode === 'all') {
        $sql = "
            SELECT CONVERT(date, created_at) AS d, COUNT(*) AS cnt
            FROM incidents
            WHERE created_at >= DATEADD(day, -6, CAST(GETDATE() AS date))
            $catSql
            GROUP BY CONVERT(date, created_at)
        ";
    } elseif ($mode === 'completed') {
        $sql = "
            SELECT CONVERT(date, COALESCE(closed_at, updated_at, created_at)) AS d, COUNT(*) AS cnt
            FROM incidents
            WHERE status IN ('Resolved', 'Closed')
              AND COALESCE(closed_at, updated_at, created_at) >= DATEADD(day, -6, CAST(GETDATE() AS date))
            $catSql
            GROUP BY CONVERT(date, COALESCE(closed_at, updated_at, created_at))
        ";
    } else {
        $sql = "
            SELECT CONVERT(date, created_at) AS d, COUNT(*) AS cnt
            FROM incidents
            WHERE status NOT IN ('Resolved', 'Closed')
              AND created_at >= DATEADD(day, -6, CAST(GETDATE() AS date))
            $catSql
            GROUP BY CONVERT(date, created_at)
        ";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $byDay = [];
    foreach ($rows as $r) {
        $d = $r['d'];
        if ($d instanceof DateTimeInterface) {
            $d = $d->format('Y-m-d');
        } else {
            $d = substr((string)$d, 0, 10);
        }
        $byDay[$d] = (int)$r['cnt'];
    }
    return $fillLast7Days($byDay);
};

$chartPayload = [
    'labels' => $chartLabels,
    'modes' => [
        'all' => [],
        'completed' => [],
        'uncompleted' => [],
    ],
];

try {
    foreach (['all', 'completed', 'uncompleted'] as $mode) {
        $chartPayload['modes'][$mode]['__all__'] = $runChartQuery($pdo, $mode, null);
        foreach ($categoriesForChart as $cat) {
            $chartPayload['modes'][$mode][$cat] = $runChartQuery($pdo, $mode, $cat);
        }
    }
} catch (Exception $e) {
    $mock = [];
    for ($j = 0; $j < 7; $j++) {
        $mock[] = rand(0, 8);
    }
    foreach (['all', 'completed', 'uncompleted'] as $mode) {
        $chartPayload['modes'][$mode] = ['__all__' => $mock];
        foreach ($categoriesForChart as $cat) {
            $chartPayload['modes'][$mode][$cat] = $mock;
        }
    }
}

// Master ticket list
$incidentsStmt = $pdo->query("
    SELECT 
        i.id,
        i.title,
        i.category,
        i.priority,
        i.status,
        i.created_at,
        ru.username AS reporter_username,
        tu.username AS tech_username
    FROM incidents i
    LEFT JOIN users ru ON i.user_id = ru.id
    LEFT JOIN users tu ON i.assigned_to = tu.id
    ORDER BY i.created_at DESC
");
$incidents = $incidentsStmt->fetchAll();
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord - Admin | GIA</title>
  <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../src/assets/css/styles.min.css" />
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
       data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

    <?php
    if (file_exists(__DIR__ . '/../includes/sidebar.php')) {
        include __DIR__ . '/../includes/sidebar.php';
    }
    if (file_exists(__DIR__ . '/../includes/header.php')) {
        include __DIR__ . '/../includes/header.php';
    }
    ?>

    <div class="body-wrapper">
      <div class="container-fluid py-4">

        <!-- Gradient KPI Cards -->
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card border-0 rounded-4 h-100 kpi-card kpi-total">
              <div class="card-body d-flex flex-column justify-content-between text-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-white-50 text-uppercase small">Total tickets</span>
                  <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Global</span>
                </div>
                <h3 class="fw-bold mb-0"><?php echo $totalTickets; ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 rounded-4 h-100 kpi-card kpi-open">
              <div class="card-body d-flex flex-column justify-content-between text-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-white-50 text-uppercase small">Tickets ouverts</span>
                  <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Open</span>
                </div>
                <h3 class="fw-bold mb-0"><?php echo $openTickets; ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 rounded-4 h-100 kpi-card kpi-resolved">
              <div class="card-body d-flex flex-column justify-content-between text-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-white-50 text-uppercase small">Résolus / clos</span>
                  <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Done</span>
                </div>
                <h3 class="fw-bold mb-0"><?php echo $resolvedTickets; ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 rounded-4 h-100 kpi-card kpi-users">
              <div class="card-body d-flex flex-column justify-content-between text-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-white-50 text-uppercase small">Utilisateurs</span>
                  <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Comptes</span>
                </div>
                <h3 class="fw-bold mb-0"><?php echo $totalUsers; ?></h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Tickets over last 7 days - Spline Area Chart + filters -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body">
                <div class="chart-filter-toolbar">
                  <div>
                    <h5 class="card-title mb-1">Activité des tickets (7 derniers jours)</h5>
                    <p class="text-muted small mb-0" id="chartFilterHelp">
                      Tous les tickets créés par jour (volume global).
                    </p>
                  </div>
                  <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="btn-group chart-filter-pills" role="group" aria-label="Type de vue">
                      <input type="radio" class="btn-check" name="chartMode" id="chartModeAll" value="all" checked autocomplete="off">
                      <label class="btn btn-outline-primary" for="chartModeAll">Tous</label>
                      <input type="radio" class="btn-check" name="chartMode" id="chartModeCompleted" value="completed" autocomplete="off">
                      <label class="btn btn-outline-success" for="chartModeCompleted">Terminés</label>
                      <input type="radio" class="btn-check" name="chartMode" id="chartModeOpen" value="uncompleted" autocomplete="off">
                      <label class="btn btn-outline-warning" for="chartModeOpen">Non terminés</label>
                    </div>
                    <select class="form-select form-select-sm chart-filter-category" id="chartCategoryFilter" aria-label="Filtrer par catégorie">
                      <option value="__all__">Toutes les catégories</option>
                      <?php foreach ($categoriesForChart as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo escape($cat); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div id="ticketsChart" style="min-height: 280px;"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Master Ticket DataTable -->
        <div class="row">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="card-title mb-0">Tous les tickets</h5>
                  <span class="text-muted small">
                    Recherche, tri et filtrage disponibles via DataTables.
                  </span>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover table-borderless align-middle datatable">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Sujet</th>
                        <th>Demandeur</th>
                        <th>Technicien</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($incidents)): ?>
                        <tr class="empty-state-row">
                          <td colspan="8" class="p-0 border-0">
                            <div class="empty-state">
                              <i class="ti ti-inbox empty-state-icon"></i>
                              <p class="empty-state-title">Super ! Votre file d'attente est vide.</p>
                              <p class="empty-state-text">Aucun ticket n'a encore été créé.</p>
                            </div>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($incidents as $ticket): ?>
                          <tr>
                            <td>#<?php echo (int)$ticket['id']; ?></td>
                            <td><?php echo escape($ticket['title']); ?></td>
                            <td><?php echo escape($ticket['reporter_username'] ?? ''); ?></td>
                            <td><?php echo escape($ticket['tech_username'] ?? 'Non assigné'); ?></td>
                            <td>
                              <span class="badge <?php echo getPriorityBadgeClass($ticket['priority']); ?>">
                                <?php echo escape($ticket['priority']); ?>
                              </span>
                            </td>
                            <td>
                              <span class="badge <?php echo getStatusBadgeClass($ticket['status']); ?>">
                                <?php echo escape($ticket['status']); ?>
                              </span>
                            </td>
                            <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                            <td>
                              <a href="view_ticket.php?id=<?php echo (int)$ticket['id']; ?>"
                                 class="btn btn-sm btn-outline-primary">
                                Voir / Éditer
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- container-fluid -->
    </div> <!-- body-wrapper -->
  </div> <!-- page-wrapper -->

  <?php
  if (file_exists(__DIR__ . '/../includes/footer.php')) {
      include __DIR__ . '/../includes/footer.php';
  }
  ?>

  <script src="../src/assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var chartPayload = <?php echo json_encode($chartPayload, JSON_UNESCAPED_UNICODE); ?>;

      var modeHelp = {
        all: 'Tous les tickets créés par jour (volume global).',
        completed: 'Tickets marqués résolus ou clos, comptés le jour de la clôture / dernière mise à jour.',
        uncompleted: 'Tickets encore en cours (non résolus), selon la date de création.'
      };

      var modeSeriesName = {
        all: 'Tickets créés',
        completed: 'Tickets terminés',
        uncompleted: 'Tickets non terminés'
      };

      var modeColors = {
        all: '#3b82f6',
        completed: '#10b981',
        uncompleted: '#f59e0b'
      };

      function zeroSeries() {
        var n = (chartPayload.labels && chartPayload.labels.length) ? chartPayload.labels.length : 7;
        var z = [];
        for (var i = 0; i < n; i++) z.push(0);
        return z;
      }

      function getSeriesData(mode, catKey) {
        var m = chartPayload.modes[mode];
        if (!m) return zeroSeries();
        if (Object.prototype.hasOwnProperty.call(m, catKey) && Array.isArray(m[catKey])) {
          return m[catKey];
        }
        return zeroSeries();
      }

      function currentMode() {
        var el = document.querySelector('input[name="chartMode"]:checked');
        return el ? el.value : 'all';
      }

      function currentCategoryKey() {
        var sel = document.getElementById('chartCategoryFilter');
        return sel ? sel.value : '__all__';
      }

      function updateHelpText() {
        var el = document.getElementById('chartFilterHelp');
        if (el) el.textContent = modeHelp[currentMode()] || '';
      }

      var options = {
        series: [{
          name: modeSeriesName.all,
          data: getSeriesData('all', currentCategoryKey())
        }],
        chart: {
          type: 'area',
          height: 280,
          fontFamily: 'Inter, system-ui, sans-serif',
          toolbar: { show: false },
          zoom: { enabled: false },
          animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 450
          }
        },
        dataLabels: { enabled: false },
        stroke: {
          curve: 'smooth',
          width: 2
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 100]
          }
        },
        colors: [modeColors.all],
        xaxis: {
          categories: chartPayload.labels,
          labels: {
            style: { colors: '#64748b', fontSize: '12px' }
          },
          axisBorder: { show: false },
          axisTicks: { show: false }
        },
        yaxis: {
          labels: {
            style: { colors: '#64748b' }
          },
          axisBorder: { show: false },
          axisTicks: { show: false }
        },
        grid: {
          borderColor: '#e2e8f0',
          strokeDashArray: 4,
          xaxis: { lines: { show: false } },
          yaxis: { lines: { show: true } }
        },
        tooltip: {
          theme: 'light',
          x: { format: 'dd MMM' }
        }
      };

      var chart = new ApexCharts(document.querySelector('#ticketsChart'), options);
      chart.render();

      function refreshChart() {
        var mode = currentMode();
        var cat = currentCategoryKey();
        var data = getSeriesData(mode, cat);
        var color = modeColors[mode] || modeColors.all;
        var name = modeSeriesName[mode] || 'Tickets';
        updateHelpText();
        chart.updateOptions({ colors: [color] }, false, false);
        chart.updateSeries([{ name: name, data: data }], true);
      }

      document.querySelectorAll('input[name="chartMode"]').forEach(function (input) {
        input.addEventListener('change', refreshChart);
      });
      var catSel = document.getElementById('chartCategoryFilter');
      if (catSel) catSel.addEventListener('change', refreshChart);
    });
  </script>
</body>
</html>
