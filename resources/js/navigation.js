document.addEventListener("DOMContentLoaded", function () {
    // Handle sidebar navigation with AJAX
    document.querySelectorAll("[data-nav-link]").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            const url = this.href;
            const navLink = this;

            // Show loading state
            const mainContent = document.getElementById("main-content");
            if (mainContent) {
                mainContent.style.opacity = "0.6";
                mainContent.style.pointerEvents = "none";
            }

            // Fetch the new page
            fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            })
                .then((response) => response.text())
                .then((html) => {
                    // Parse the response HTML
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, "text/html");

                    // Extract the main content
                    const newMainContent = newDoc.getElementById("main-content");
                    const newHeader = newDoc.getElementById("page-header");

                    if (newMainContent) {
                        // Replace the main content with smooth transition
                        mainContent.innerHTML = newMainContent.innerHTML;

                        // Replace header if it exists
                        if (newHeader) {
                            const currentHeader = document.getElementById("page-header");
                            if (currentHeader) {
                                currentHeader.innerHTML = newHeader.innerHTML;
                            }
                        }

                        // Update URL
                        window.history.pushState({ path: url }, "", url);

                        // Restore content visibility
                        if (mainContent) {
                            mainContent.style.opacity = "1";
                            mainContent.style.pointerEvents = "auto";
                        }

                        // Update active nav link
                        document.querySelectorAll('[data-nav-link]').forEach((el) => {
                            el.classList.remove('bg-gray-200');
                        });
                        navLink.classList.add('bg-gray-200');

                        // Remove hover:bg-gray-400 from active, add to others
                        document.querySelectorAll('[data-nav-link]').forEach((el) => {
                            if (el === navLink) {
                                el.classList.remove('hover:bg-gray-400');
                            } else {
                                if (!el.classList.contains('hover:bg-gray-400')) {
                                    el.classList.add('hover:bg-gray-400');
                                }
                            }
                        });

                        // Scroll to top
                        window.scrollTo(0, 0);
                    }
                })
                .catch((error) => {
                    console.error("Error loading page:", error);
                    // Fallback: navigate normally
                    window.location.href = url;
                });
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener("popstate", function (e) {
        if (e.state && e.state.path) {
            // Reload the page at that URL
            window.location.href = e.state.path;
        }
    });
});
