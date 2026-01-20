(function ($) {
    'use strict';

    // Global action handler - THIS MUST BE OUTSIDE document.ready
    $(document).on('click', '[data-action]', function (e) {
        e.preventDefault();
        const action = $(this).data('action');

        // Call the function by name
        if (typeof window[action] === 'function') {
            window[action]();
        } else {
            console.error('Function not found:', action);
        }
    });

    // LAUNCH DESIGNER BUTTON - Works on Product Pages
    $(document).on('click', '.swp-launch-designer', function (e) {
        e.preventDefault();

        const productId = $(this).data('product-id');

        if (!productId) {
            alert('Product ID missing');
            return;
        }

        // Show loading state
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...');

        $.ajax({
            url: swp_ls_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'swp_ls_set_product',
                nonce: swp_ls_vars.nonce,
                product_id: productId
            },
            success: function (res) {
                if (res.success && res.data.designer_url) {
                    window.location.href = res.data.designer_url;
                } else {
                    alert(res.data || 'Failed to launch designer');
                    $btn.prop('disabled', false).html('Launch Designer');
                }
            },
            error: function (err) {
                console.error('AJAX error:', err);
                alert('Network error. Please try again.');
                $btn.prop('disabled', false).html('Launch Designer');
            }
        });
    });

})(jQuery);