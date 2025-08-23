
        document.addEventListener("DOMContentLoaded", function() {
            const rows = document.querySelectorAll("tbody tr");
            const rowsPerPage = 10;
            let currentPage = 1;

            function showPage(page) {
                rows.forEach((row, index) => {
                    row.style.display =
                        index >= (page - 1) * rowsPerPage && index < page * rowsPerPage ?
                        "" :
                        "none";
                });
            }

            function createPagination() {
                const totalPages = Math.ceil(rows.length / rowsPerPage);
                const pagination = document.getElementById("pagination");
                pagination.innerHTML = "";

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.innerText = i;
                    btn.className = "px-3 py-1 border mx-1 " +
                        (i === currentPage ? "bg-blue-500 text-white cursor-pointer" : "bg-white text-gray-700 cursor-pointer");

                    btn.addEventListener("click", function() {
                        currentPage = i;
                        showPage(currentPage);
                        createPagination();
                    });

                    pagination.appendChild(btn);
                }
            }

            showPage(currentPage);
            createPagination();
        });
