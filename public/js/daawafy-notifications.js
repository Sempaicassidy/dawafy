/**
 * Daawafy jQuery Real-Time Notification & Pop-Up Engine
 * Handles Toast Pop-ups for all actions: Sales Successful, Added Successfully, Paid Successfully, Danger Alerts.
 */

(function($) {
    'use strict';

    // Ensure container exists
    $(document).ready(function() {
        if ($('#daawafy-toast-container').length === 0) {
            $('body').append('<div id="daawafy-toast-container"></div>');
        }
    });

    /**
     * Core Toast Notification Trigger
     * @param {string} type - 'success', 'warning', 'danger', 'info'
     * @param {string} title - Title text
     * @param {string} message - Description message
     * @param {number} duration - Time in milliseconds (default 4000)
     */
    window.showDaawafyToast = function(type, title, message, duration) {
        duration = duration || 4000;
        
        let iconClass = 'bi-check-circle-fill';
        if (type === 'warning') iconClass = 'bi-exclamation-triangle-fill';
        if (type === 'danger') iconClass = 'bi-x-circle-fill';
        if (type === 'info') iconClass = 'bi-info-circle-fill';

        const toastId = 'toast-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        
        const toastHtml = `
            <div id="${toastId}" class="daawafy-toast toast-${type}">
                <div class="toast-icon">
                    <i class="bi ${iconClass}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button type="button" class="btn-close btn-sm ms-auto" onclick="$('#${toastId}').fadeOut(200, function(){ $(this).remove(); });"></button>
                <div class="toast-progress-bar" style="animation-duration: ${duration}ms;"></div>
            </div>
        `;

        const $toast = $(toastHtml);
        $('#daawafy-toast-container').append($toast);

        // Auto remove after duration
        setTimeout(function() {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, duration);
    };

    /**
     * Action-specific notification shortcuts
     */
    window.notifySalesSuccess = function(invoiceNo, totalAmount) {
        let amtFormatted = typeof totalAmount === 'number' ? totalAmount.toLocaleString() : totalAmount;
        window.showDaawafyToast(
            'success',
            '🎉 Sales Successful!',
            `Receipt <strong>#${invoiceNo || 'REC-' + Math.floor(Math.random()*10000)}</strong> completed. Total: <strong>TZS ${amtFormatted}</strong>`,
            4500
        );
    };

    window.notifyAddedSuccess = function(itemName) {
        window.showDaawafyToast(
            'success',
            '✅ Added Successfully!',
            `<strong>${itemName || 'Item'}</strong> has been saved and updated in inventory.`,
            4000
        );
    };

    window.notifyPaidSuccess = function(amount, method) {
        let amtFormatted = typeof amount === 'number' ? amount.toLocaleString() : amount;
        window.showDaawafyToast(
            'success',
            '💵 Paid Successfully!',
            `Payment of <strong>TZS ${amtFormatted}</strong> processed via <strong>${method || 'Cash'}</strong>.`,
            4500
        );
    };

    window.notifyDanger = function(title, message) {
        window.showDaawafyToast(
            'danger',
            title || '⚠️ Danger / Error!',
            message || 'An error occurred. Please check stock or permissions.',
            5000
        );
    };

    window.notifyWarning = function(title, message) {
        window.showDaawafyToast(
            'warning',
            title || '⚡ Alert Notice',
            message || 'Attention required.',
            4500
        );
    };

})(jQuery);
