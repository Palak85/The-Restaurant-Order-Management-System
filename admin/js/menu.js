// Handle form submission
$('#addMenuItemForm').on('submit', function(e) {
    e.preventDefault();
    
    // Create FormData object
    const formData = new FormData(this);
    
    // Show loading state
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
    
    // Send AJAX request
    $.ajax({
        url: 'add_menu_item.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Reset form
                $('#addMenuItemForm')[0].reset();
                
                // Refresh menu items list
                loadMenuItems();
            } else {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        },
        error: function(xhr, status, error) {
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to add menu item. Please try again.'
            });
        },
        complete: function() {
            // Reset button state
            $('#submitBtn').prop('disabled', false).html('Add Item');
        }
    });
}); 