jQuery(document).ready(function ($) {
    // PRICE, QTY & VARIANT HANDLING
    // ===============================

    // Make the total calculation function global
    window.swpLsUpdateTotal = function () {
        const $totalEl = $('#totalPrice');
        let qty = parseInt($('#swp-ls-qty').val()) || 1;
        let price = parseFloat($totalEl.data('base-price')) || 0;

        // Get currency symbol from data attribute
        const currencySymbol = $totalEl.data('currency-symbol') || '$';

        // Check if variation is selected and use its price
        const $variant = $('#swp-ls-variation');
        if ($variant.length) {
            const $selectedOption = $variant.find('option:selected');
            const variantPrice = $selectedOption.data('price');

            if (variantPrice && $selectedOption.val() !== '') {
                price = parseFloat(variantPrice);
            }
        }

        const total = price * qty;

        // Format the price properly
        const formattedTotal = currencySymbol + total.toFixed(2);

        // Update the displayed total with animation
        $totalEl.fadeOut(100, function () {
            $(this).html(formattedTotal).fadeIn(100);
        });

        return total; // <-- return total so other JS can use it
    };

    // Quantity Control Functions
    function swpLsIncreaseQty() {
        const $qtyInput = $('#swp-ls-qty');
        let currentQty = parseInt($qtyInput.val()) || 1;
        $qtyInput.val(currentQty + 1).trigger('change');

        // Add visual feedback
        const $plusBtn = $('#qtyPlus');
        $plusBtn.css('transform', 'scale(0.9)');
        setTimeout(() => $plusBtn.css('transform', 'scale(1)'), 100);
    }

    function swpLsDecreaseQty() {
        const $qtyInput = $('#swp-ls-qty');
        let currentQty = parseInt($qtyInput.val()) || 1;

        if (currentQty > 1) {
            $qtyInput.val(currentQty - 1).trigger('change');

            // Add visual feedback
            const $minusBtn = $('#qtyMinus');
            $minusBtn.css('transform', 'scale(0.9)');
            setTimeout(() => $minusBtn.css('transform', 'scale(1)'), 100);
        }
    }

    function swpLsUpdateMinusButton() {
        const $qtyInput = $('#swp-ls-qty');
        const $minusBtn = $('#qtyMinus');
        let currentQty = parseInt($qtyInput.val()) || 1;

        // Disable minus button if quantity is 1
        if (currentQty <= 1) {
            $minusBtn.prop('disabled', true).css('opacity', '0.4');
        } else {
            $minusBtn.prop('disabled', false).css('opacity', '1');
        }
    }

    // Initial calculation
    window.swpLsUpdateTotal();
    swpLsUpdateMinusButton();

    // Update on quantity change
    $('#swp-ls-qty').on('change keyup', function () {
        window.swpLsUpdateTotal();
        swpLsUpdateMinusButton();
    });

    // Update on variation change with animation
    $('#swp-ls-variation').on('change', function () {
        const $variant = $(this);

        // Add visual feedback
        $variant.css('border-color', '#3b82f6');
        setTimeout(() => $variant.css('border-color', ''), 300);

        window.swpLsUpdateTotal();
    });

    // Plus button click
    $('#qtyPlus').on('click', function (e) {
        e.preventDefault();
        swpLsIncreaseQty();
    });

    // Minus button click
    $('#qtyMinus').on('click', function (e) {
        e.preventDefault();
        swpLsDecreaseQty();
    });

    // Keyboard shortcuts for quantity
    $(document).on('keydown', function (e) {
        if ($('#swp-ls-qty').is(':focus') || !$('input, select, textarea').is(':focus')) {
            if (e.key === '+' || e.key === '=') {
                e.preventDefault();
                swpLsIncreaseQty();
            }
            if (e.key === '-') {
                e.preventDefault();
                swpLsDecreaseQty();
            }
        }
    });

    // Prevent manual input (optional)
    $('#swp-ls-qty').on('keypress', function (e) {
        e.preventDefault();
        return false;
    });

    // Add hover effects for variant selector
    $('#swp-ls-variation').hover(
        function () {
            if (!$(this).is(':disabled')) {
                $(this).parent().find('.variant-icon').css({
                    'color': '#3b82f6',
                    'transform': 'translateY(-50%) scale(1.1)'
                });
            }
        },
        function () {
            $(this).parent().find('.variant-icon').css({
                'color': '#64748b',
                'transform': 'translateY(-50%) scale(1)'
            });
        }
    );
});
