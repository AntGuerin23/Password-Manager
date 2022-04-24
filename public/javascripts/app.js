$(document).ready(function () {
    $('#dataTable').dataTable({
        "pagingType": "simple_numbers",
        "ordering": false,
        "responsive": true,
        "scrollX": false,
        "lengthChange": false,
        "searching": false,
        "info": false,
        "language": {
            "emptyTable": "There are no active connections"
        },
        "columns": [
            {"width": "25%", "className": "text-center align-vertical"},
            {"width": "25%", "className": "text-center align-vertical"},
            {"width": "25%", "className": "text-center align-vertical"},
            {"width": "25%", "className": "text-center align-vertical"},
            {"width": "25%", "className": "text-center align-vertical"}
        ],
        "paging": false
    })
});

$("[data-action='delete']").on("submit", (e) => {
    e.preventDefault();
    console.log()
    const target = e.target
    $("#deleteModal").modal('toggle')
    $("#modalDelete").on("click", '', { 'target' : target  }, (e, target) => {
        console.log("awd")
        const form = e.data.target
        form.submit();
    })
})