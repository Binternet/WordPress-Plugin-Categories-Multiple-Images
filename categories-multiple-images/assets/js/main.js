
jQuery(document).ready(function($) {
    
    // Upload Button
    $('.cmi-button-upload').on('click',function(e){
        e.preventDefault();
        
        // Set parent according to add/edit mode
        var $parent = ( cmi_config.mode == 'add' ) ? $(this).parent('.form-field') : $(this).parent('td'),
            $input = $parent.find('input[type="text"]').eq(0),
            $img = $parent.find('img').eq(0),
            frame = false;
        
        if ( $input.length == 0 ) {
            // Wops, something is wrong
            alert('Cannot select the correct filename, aborting');
            return false;
        }
        
        
        if (frame) {
        	frame.open();
        	return;
        }
        
        frame = wp.media();
        
        // Register Event
        frame.on( "select", function() {
        	// Grab the selected attachment.
        	var attachment = frame.state().get("selection").first();
            
            // Show the full URL for the file
            $input.val( attachment.attributes.url );
            
            // Show the image
            $img.attr( 'src', attachment.attributes.url );
            
            // Close the media frame
        	frame.close();
        });
        
        // Show media frame
        frame.open();
    });


    // Remove Button
    $('.cmi-button-remove').on('click',function(e){
       e.preventDefault();
       
       var $parent = $(this).parent('td'),
           $input = $parent.find('input'),
           $img = $parent.find('img');
       
        // Clear
        $input.val('');
        $img.attr('src', cmi_config.placeholder );
    });


    
    
    
});
