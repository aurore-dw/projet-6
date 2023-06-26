$(document).ready(function() {
            // Soumettre le formulaire avec les images à supprimer sélectionnées
            $('form').submit(function() {
                // Sélectionner les cases à cocher cochées
                var selectedPictures = $('input[name="remove_pictures[]"]:checked').map(function() {
                    return $(this).val();
                }).get();

                // Ajouter les valeurs sélectionnées à un champ caché dans le formulaire
                $('<input>').attr({
                    type: 'hidden',
                    name: 'selected_pictures',
                    value: selectedPictures.join(',')
                }).appendTo($(this));

                return true; // Continuer la soumission du formulaire
            });
        });