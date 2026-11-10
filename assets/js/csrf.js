// -------------------------------------------------------------------------------------
// CSRF token for AJAX saves
//
// Pages that save through the older endpoints (GENERALFunctions.php, ACCOUNTManagement.php,
// LOCALAttractionFunctions.php, sponsor_management.php) print the session's token in
//     <meta name="csrf-token" content="...">
// and load this file after jQuery. Every jQuery POST then carries it as csrf_token, unless
// the request already sends one. The endpoints refuse a save without it.
// -------------------------------------------------------------------------------------
$.ajaxPrefilter(function(options) {
    var token = $('meta[name="csrf-token"]').attr('content');
    if (!token || (options.type || '').toUpperCase() !== 'POST') {
        return;
    }
    if (window.FormData && options.data instanceof FormData) {
        if (!options.data.has('csrf_token')) {
            options.data.append('csrf_token', token);
        }
    } else if (typeof options.data === 'string' || options.data == null) {
        // jQuery has already turned an object into a query string by this point
        var data = options.data || '';
        if (!/(^|&)csrf_token=/.test(data)) {
            options.data = data + (data ? '&' : '') + 'csrf_token=' + encodeURIComponent(token);
        }
    }
});
