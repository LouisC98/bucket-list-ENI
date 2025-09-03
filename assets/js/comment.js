import {showNotification} from "./utils.js";

document.addEventListener("turbo:load", function() {
    // FORM D'AJOUT DE COMMENTAIRE
    const showNewCommentFormBtn = document.getElementById("showNewCommentFormBtn");
    const newCommentForm = document.getElementById("newCommentForm");
    showNewCommentFormBtn.addEventListener("click", function(e) {
        newCommentForm.classList.toggle("hidden");
        if (!newCommentForm.classList.contains("hidden")) {
            showNewCommentFormBtn.textContent = 'Annuler le commentaire';
        } else {
            showNewCommentFormBtn.textContent = 'Ajouter un commentaire';
        }
    })

    // EDITION COMMENTAIRE - Event listeners sur les boutons
    const editCommentBtns = document.querySelectorAll("[id^='editCommentBtn-']");
    editCommentBtns.forEach(btn => {
        btn.addEventListener("click", async function(e) {
            e.preventDefault();
            const commentId = btn.dataset.commentId;
            await editComment(commentId);
        })
    })

    // Variable pour stocker le contenu original (pour l'annulation)
    let originalCardContent = {};

    // FONCTION POUR AFFICHER LE FORMULAIRE D'ÉDITION
    async function editComment(commentId) {
        const originalCard = document.getElementById("comment-"+commentId);

        // Sauvegarder le contenu original pour pouvoir l'annuler
        originalCardContent[commentId] = originalCard.outerHTML;

        try {
            const response = await fetch("/comment/" + commentId + "/edit");

            if (response.ok) {
                const data = await response.json();

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                const newForm = tempDiv.firstElementChild;

                newForm.id = "comment-" + commentId;
                newForm.classList.remove("hidden");

                originalCard.replaceWith(newForm);

                // Event listener pour la soumission
                const form = newForm.querySelector('form');
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    await submitEditComment(commentId, form);
                });

                // Event listener pour l'annulation
                const cancelBtn = newForm.querySelector('#cancelEditBtn-' + commentId);
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        cancelEdit(commentId);
                    });
                }

            } else {
                console.error('Erreur lors du chargement du formulaire:', response);
            }
        } catch (error) {
            console.error('Erreur réseau:', error);
        }
    }

    // FONCTION POUR ANNULER L'ÉDITION
    function cancelEdit(commentId) {
        const currentElement = document.getElementById("comment-" + commentId);

        // Restaurer le contenu original
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = originalCardContent[commentId];
        const originalCard = tempDiv.firstElementChild;

        currentElement.replaceWith(originalCard);

        // Nettoyer la sauvegarde
        delete originalCardContent[commentId];

        // Re-ajouter l'event listener sur le bouton "Modifier" restauré
        const editBtn = originalCard.querySelector("[id^='editCommentBtn-']");
        if (editBtn) {
            editBtn.addEventListener("click", async function(e) {
                e.preventDefault();
                const commentId = editBtn.dataset.commentId;
                await editComment(commentId);
            });
        }
    }

    // Fonction pour soumettre le formulaire d'édition
    async function submitEditComment(commentId, form) {
        const formData = new FormData(form);

        try {
            const response = await fetch("/comment/" + commentId + "/edit", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                const currentElement = document.getElementById("comment-" + commentId);

                if (data.success) {
                    // Remplacer par la carte mise à jour
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const updatedCard = tempDiv.firstElementChild;

                    currentElement.replaceWith(updatedCard);
                    showNotification("Commentaire modifié !")
                    // Nettoyer la sauvegarde
                    delete originalCardContent[commentId];

                    // Re-ajouter l'event listener sur le nouveau bouton "Modifier"
                    const editBtn = updatedCard.querySelector("[id^='editCommentBtn-']");
                    if (editBtn) {
                        editBtn.addEventListener("click", async function(e) {
                            e.preventDefault();
                            const commentId = editBtn.dataset.commentId;
                            await editComment(commentId);
                        });
                    }
                } else {
                    // erreur, réafficher le formulaire avec erreurs
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const formWithErrors = tempDiv.firstElementChild;
                    formWithErrors.id = "comment-" + commentId;

                    currentElement.replaceWith(formWithErrors);

                    // Re-ajouter les event listeners
                    const newForm = formWithErrors.querySelector('form');
                    newForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        await submitEditComment(commentId, newForm);
                    });

                    const cancelBtn = formWithErrors.querySelector('#cancelEditBtn-' + commentId);
                    if (cancelBtn) {
                        cancelBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            cancelEdit(commentId);
                        });
                    }
                }
            } else {
                console.error('Erreur lors de la soumission:', response);
            }
        } catch (error) {
            console.error('Erreur réseau:', error);
        }
    }

    // Suppression commentaire
    const deleteCommentBtns = document.querySelectorAll("[id^='deleteCommentBtn-']");
    deleteCommentBtns.forEach(btn => {
        btn.addEventListener("click", async function(e) {
            e.preventDefault();

            const commentId = btn.dataset.commentId;

            // Demander confirmation
            if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
                await deleteComment(commentId);
            }
        })
    })
    async function deleteComment(commentId) {
        try {
            const response = await fetch("/comment/" + commentId, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();

                if (data.success) {
                    const commentElement = document.getElementById("comment-" + commentId);
                    commentElement.remove()

                    showNotification("Commentaire supprimé", "error")
                } else {
                    alert('Erreur lors de la suppression: ' + (data.error || 'Erreur inconnue'));
                }
            } else {
                const data = await response.json();
                alert('Erreur: ' + (data.error || 'Erreur de suppression'));
            }
        } catch (error) {
            console.error('Erreur réseau:', error);
            alert('Erreur de connexion lors de la suppression');
        }
    }
})