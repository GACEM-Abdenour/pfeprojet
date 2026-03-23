<?php
/**
 * Global footer scripts - jQuery, Bootstrap, DataTables, and auto-init.
 */
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-3gJwYp4gkP7hJr5qfhBF3nfjLr3zN3n6t3mq6bY0yNc="
        crossorigin="anonymous"></script>

<script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  // Auto-initialize DataTables on any table with the .datatable class
  $(document).ready(function () {
    $('.datatable').each(function () {
      if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
          }
        });
      }
    });
  });
</script>

