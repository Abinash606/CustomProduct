jQuery(document).ready(function ($) {
    'use strict';
    $(document).on('change', '.swp-design-label-toggle', function () {
        const checkbox = $(this);
        const productId = checkbox.data('product-id');
        const isChecked = checkbox.is(':checked');
        const wrapper = checkbox.closest('.swp-toggle-wrapper');
        wrapper.addClass('loading');
        checkbox.prop('disabled', true);

        $.ajax({
            url: swp_ls_admin.ajaxurl || ajaxurl,
            type: 'POST',
            data: {
                action: 'swp_toggle_design_label',
                nonce: swp_ls_admin.nonce,
                product_id: productId,
                enabled: isChecked ? 'yes' : 'no'
            },
            error: function (xhr, status, error) {
                checkbox.prop('checked', !isChecked);
                alert('Connection error. Please try again.');
            },
            complete: function () {
                wrapper.removeClass('loading');
                checkbox.prop('disabled', false);
            }
        });
    });

    $(document).on('keydown', '.swp-toggle-label', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            const checkbox = $(this).prev('.swp-design-label-toggle');
            checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
        }
    });

    $(document).on('mouseenter', '.swp-toggle-label', function () {
        $(this).css('opacity', '0.85');
    }).on('mouseleave', '.swp-toggle-label', function () {
        if (!$(this).closest('.swp-toggle-wrapper').hasClass('loading')) {
            $(this).css('opacity', '1');
        }
    });
});