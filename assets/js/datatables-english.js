/* Shared English labels. Modules may override labels for their own table context. */
(function ($) {
    'use strict';
    if (!$ || !$.fn || !$.fn.dataTable) return;

    var language = {
        emptyTable: 'No data available in table',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: 'Showing 0 to 0 of 0 entries',
        infoFiltered: '(filtered from _MAX_ total entries)',
        infoPostFix: '',
        lengthMenu: 'Show _MENU_ entries',
        loadingRecords: 'Loading...',
        processing: 'Processing...',
        search: 'Search:',
        zeroRecords: 'No matching records found',
        url: '',
        paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' },
        aria: { sortAscending: ': activate to sort column ascending', sortDescending: ': activate to sort column descending' }
    };
    $.extend(true, $.fn.dataTable.defaults, { language: language });
    // Legacy tables still use the Hungarian language keys.
    $.extend(true, $.fn.dataTable.defaults.oLanguage, {
        sEmptyTable: language.emptyTable, sInfo: language.info, sInfoEmpty: language.infoEmpty,
        sInfoFiltered: language.infoFiltered, sInfoPostFix: '', sLengthMenu: language.lengthMenu,
        sLoadingRecords: language.loadingRecords, sProcessing: language.processing, sSearch: language.search,
        sZeroRecords: language.zeroRecords, sUrl: '',
        oPaginate: { sFirst: 'First', sLast: 'Last', sNext: 'Next', sPrevious: 'Previous' },
        oAria: { sSortAscending: language.aria.sortAscending, sSortDescending: language.aria.sortDescending }
    });
}(window.jQuery));
