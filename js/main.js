jQuery(document).ready(function ($) {
    var frame;
    $('#tm_openai_model_file').click(function() { 
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( frame ) {
          frame.open();
          return;
        }

        // Create a new media frame
        frame = wp.media({
          title: 'Select or Upload JSONL File',
          button: {
            text: 'Use this File'
          },
          multiple: false 
        });


        // When an image is selected in the media frame...
        frame.on( 'select', function() {

          // Get media attachment details from the frame state
          var attachment = frame.state().get('selection').first().toJSON();

          // Send the attachment id to our input field
          $('#wb_additional_file').val( attachment.url );
        });
    });

});