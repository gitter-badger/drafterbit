(function($, drafTerbit) {
    
    var dt = $("#log-data-table").dataTable({
                "oLanguage": {
                "sLengthMenu": "Showing _MENU_ records per page",
                "sSearch": "Search: _INPUT_",
                },
                "columnDefs": [
                    {'orderable': false, 'searchable':false, 'targets':[0]}
                ]
            });

    drafTerbit.replaceDTSearch(dt);

    $('#log-checkall').checkAll({showIndeterminate:true});
    
})(jQuery,drafTerbit);