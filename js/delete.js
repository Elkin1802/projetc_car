    //  Delete alert and button 

  
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const id = this.getAttribute('data-id');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('table.php?delete=' + id)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === "ok") {
                                        Swal.fire({
                                            title: 'Eliminado',
                                            text: 'El préstamo ha sido eliminado correctamente.',
                                            icon: 'success',
                                            confirmButtonText: 'Aceptar'
                                        }).then(() => {
                                            location.reload(); // 🔄 recarga automáticamente
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'No se pudo eliminar el préstamo.',
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar'
                                        });
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Error en la petición al servidor.', 'error');
                                });
                        }
                    });
                });
            });
        });
