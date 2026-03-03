document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");

    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            const filter = searchInput.value.toLowerCase();
            const cards = document.querySelectorAll(".student-card, .table-row, .searchable-item");

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                // Find nearest column container if it's a grid item, else standard hide
                const colContainer = card.closest(".col-md-6, .col-lg-4, .searchable-col");
                const target = colContainer ? colContainer : card;

                if (text.includes(filter)) {
                    target.style.display = "";
                } else {
                    target.style.display = "none";
                }
            });

            // Ocultar cabeceras de niveles/grupos vacías (específico para ver_calificaciones)
            const groupCards = document.querySelectorAll(".student-group-card");
            if (groupCards.length > 0) {
                groupCards.forEach(group => {
                    const visibleCards = group.querySelectorAll(".col-md-6[style=''], .col-md-6:not([style*='none'])").length;
                    group.style.display = visibleCards > 0 ? "" : "none";

                    const parentNivel = group.closest(".mb-5");
                    if (parentNivel) {
                        const visibleGroups = parentNivel.querySelectorAll(".student-group-card:not([style*='none'])").length;
                        parentNivel.style.display = visibleGroups > 0 ? "" : "none";
                    }
                });
            }
        });
    }
});
