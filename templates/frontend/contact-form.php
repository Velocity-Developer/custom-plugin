<?php
/**
 * Frontend Template: Contact Form
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-wrapper" style="font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div x-data="contactForm()" class="bg-white rounded-xl shadow-lg p-8 max-w-lg mx-auto border border-gray-100">

        <!-- Notifications -->
        <div x-show="success" x-transition class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700" x-text="message"></p>
                </div>
            </div>
        </div>

        <div x-show="error" x-transition class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700" x-text="message"></p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6">
            <div>
                <label for="cp_name" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Nama', 'custom-plugin'); ?></label>
                <input type="text" id="cp_name" x-model="formData.name" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out">
            </div>

            <div>
                <label for="cp_email" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Email', 'custom-plugin'); ?></label>
                <input type="email" id="cp_email" x-model="formData.email" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out">
            </div>

            <div>
                <label for="cp_message" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Pesan', 'custom-plugin'); ?></label>
                <textarea id="cp_message" x-model="formData.message" rows="4" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"></textarea>
            </div>

            <div>
                <button type="submit" :disabled="loading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out" style="color: white;">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? '<?php esc_attr_e('Mengirim...', 'custom-plugin'); ?>' : '<?php esc_attr_e('Kirim Pesan', 'custom-plugin'); ?>'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('contactForm', () => ({
            formData: {
                name: '',
                email: '',
                message: ''
            },
            loading: false,
            success: false,
            error: false,
            message: '',

            async submitForm() {
                this.loading = true;
                this.error = false;
                this.success = false;
                this.message = '';

                try {
                    const response = await fetch(customPlugin.apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': customPlugin.nonce
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.success = true;
                        this.message = result.message;
                        this.formData = {
                            name: '',
                            email: '',
                            message: ''
                        };
                    } else {
                        throw new Error(result.message || 'Something went wrong');
                    }
                } catch (err) {
                    this.error = true;
                    this.message = err.message;
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
