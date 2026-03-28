// Filtrage du tableau des transactions
function filterTable() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("transactionTable");
    if (table) {
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
}

// Mise à jour automatique du type de transaction selon la catégorie
function updateTransactionType() {
    var categorySelect = document.getElementById('category_id');
    var typeSelect = document.getElementById('transaction_type');
    
    if (categorySelect && typeSelect) {
        var selectedOption = categorySelect.options[categorySelect.selectedIndex];
        var categoryType = selectedOption.getAttribute('data-type');
        
        if (categoryType === 'expense') {
            typeSelect.value = 'expense';
        } else if (categoryType === 'income') {
            typeSelect.value = 'income';
        }
    }
}

// Mise à jour pour le calendrier
function updateCalendarTransactionType(day) {
    var categorySelect = document.querySelector('#form-day-' + day + ' select[name="category_id"]');
    var typeSelect = document.getElementById('calendar_type_' + day);
    
    if (categorySelect && typeSelect) {
        var selectedOption = categorySelect.options[categorySelect.selectedIndex];
        var categoryType = selectedOption.getAttribute('data-type');
        
        if (categoryType === 'expense') {
            typeSelect.value = 'expense';
        } else if (categoryType === 'income') {
            typeSelect.value = 'income';
        }
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le type de transaction si le formulaire existe
    updateTransactionType();
    
    // Gestion du graphique Chart.js si présent
    var ctx = document.getElementById('balanceChart');
    if (ctx && typeof Chart !== 'undefined') {
        console.log('Chart initialized');
    }
});