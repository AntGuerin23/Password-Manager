// Call the dataTables jQuery plugin
$(document).ready(function () {
    $('#dataTable').dataTable({
        "pagingType": "simple_numbers",
        "ordering": false,
        "scrollX": false,
        "lengthChange": false,
        "searching": false,
        "info": false,
        "columns": [
            {"width": "30%"},
            {"width": "50%"},
            {"width": "6%", "className": "text-center"}
        ],
        "paging": false
    })
});
