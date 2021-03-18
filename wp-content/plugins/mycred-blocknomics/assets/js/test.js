// alert( 'the plugin wp_enqueue_scripts work' );


$(document).ready(function () {
    $('select').on('change', function () {
        if (this.value === 'No') {
            $('input.donor-name').prop("disabled", false);
            $('input.donor-name').attr("placeholder", "Your Name");
            $('input.donor-name').attr("value", "");

        } else {
            let r = "Anonymous_" + Math.random().toString(36).substring(2);
            $('input.donor-name').prop("disabled", true);
            $('input.donor-name').attr("placeholder", r);
            $('input.donor-name').attr("value", r);
        }
    });
})