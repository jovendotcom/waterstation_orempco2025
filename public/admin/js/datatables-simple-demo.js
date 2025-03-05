window.addEventListener('DOMContentLoaded', event => {
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            searchable: true,  // Enable/disable searching
            fixedHeight: false, // Fix the height of the table
            perPage: 20,       // Number of rows per page
            perPageSelect: [20, 30, 40, 50, 100],  // Options for rows per page selection
            labels: {
                placeholder: "Search...", // Placeholder text for search input
                perPage: "rows per page", // Label for per-page dropdown
                noRows: "No entries to display",   // Text when there are no rows
                info: "Showing {start} to {end} of {rows} entries",  // Footer info text
            }
        });
    }
});

