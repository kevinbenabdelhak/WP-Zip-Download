<?php 


if (!defined('ABSPATH')) {
    exit; 
}



add_action('admin_enqueue_scripts', 'telecharger_zip_enqueue_scripts');
function telecharger_zip_enqueue_scripts($hook) {
    if ($hook !== 'upload.php') {
        return;
    }

    wp_enqueue_script('jquery');
    wp_add_inline_script('jquery', '
    jQuery(document).ready(function($) {
        if ($("select[name=\'action\'] option[value=\'telecharger_zip\']").length === 0) {
            $("select[name=\'action\'], select[name=\'action2\']").append(\'<option value="telecharger_zip">' . __('Télécharger en .ZIP', 'textdomain') . '</option>\');
        }

        $(document).on("click", "#doaction, #doaction2", function(e) {
            var action = $("select[name=\'action\']").val() !== "-1" ? $("select[name=\'action\']").val() : $("select[name=\'action2\']").val();

            if (action !== "telecharger_zip") return;

            e.preventDefault();

            var attachment_ids = [];
            $("tbody th.check-column input[type=\'checkbox\']:checked").each(function() {
                attachment_ids.push($(this).val());
            });

            if (attachment_ids.length === 0) {
                alert("' . __('Aucun média sélectionné.', 'textdomain') . '");
                return;
            }

            $("#bulk-action-loader").remove();
            $("#doaction, #doaction2").after("<div id=\'bulk-action-loader\'><span class=\'spinner is-active\' style=\'margin-left: 10px;\'></span></div>");

            $.ajax({
                url: wpc2a_ajax.ajax_url,
                method: "POST",
                data: {
                    action: "telecharger_zip",
                    nonce: wpc2a_ajax.nonce,
                    media: attachment_ids
                },
                success: function(response) {
                    $("#bulk-action-loader").remove();
                    if (response.success) {
                        window.location.href = response.data;
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    $("#bulk-action-loader").remove();
                    alert("' . __('Une erreur s\'est produite.', 'textdomain') . '");
                }
            });
        });
    });');

    wp_localize_script('jquery', 'wpc2a_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('telecharger_zip_nonce')
    ]);
}

add_filter('bulk_actions-upload', 'ajouter_action_telecharger_zip_option');
function ajouter_action_telecharger_zip_option($actions) {
    $actions['telecharger_zip'] = __('Télécharger en .ZIP', 'textdomain');
    return $actions;
}