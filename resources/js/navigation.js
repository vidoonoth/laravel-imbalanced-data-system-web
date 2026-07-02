document.addEventListener("DOMContentLoaded", function () {
    const setContentLoading = function (isLoading) {
        const mainContent = document.getElementById("main-content");

        if (!mainContent) {
            return;
        }

        mainContent.style.opacity = isLoading ? "0.6" : "1";
        mainContent.style.pointerEvents = isLoading ? "none" : "auto";
    };

    const initAlpineTree = function (element) {
        if (element && window.Alpine && typeof window.Alpine.initTree === "function") {
            window.Alpine.initTree(element);
        }
    };

    const replacePageSection = function (selector, newDocument, initializeAlpine = false) {
        const currentElement = document.querySelector(selector);
        const newElement = newDocument.querySelector(selector);

        if (!currentElement || !newElement) {
            return;
        }

        currentElement.innerHTML = newElement.innerHTML;

        if (initializeAlpine) {
            initAlpineTree(currentElement);
        }
    };

    const renderPage = function (html) {
        const parser = new DOMParser();
        const newDocument = parser.parseFromString(html, "text/html");
        const mainContent = document.getElementById("main-content");
        const newMainContent = newDocument.getElementById("main-content");

        if (!mainContent || !newMainContent) {
            return false;
        }

        mainContent.innerHTML = newMainContent.innerHTML;
        initAlpineTree(mainContent);

        replacePageSection("aside nav", newDocument);
        replacePageSection("#page-breadcrumbs", newDocument);
        replacePageSection("#page-header", newDocument, true);

        if (newDocument.title) {
            document.title = newDocument.title;
        }

        mainContent.scrollTo(0, 0);
        window.scrollTo(0, 0);

        return true;
    };

    const navigateTo = function (url, options = {}) {
        const shouldPushState = options.pushState !== false;

        setContentLoading(true);

        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Navigation request failed.");
                }

                return response.text();
            })
            .then(function (html) {
                if (!renderPage(html)) {
                    window.location.href = url;
                    return;
                }

                if (shouldPushState) {
                    window.history.pushState({ path: url }, "", url);
                }

                setContentLoading(false);
            })
            .catch(function (error) {
                console.error("Error loading page:", error);
                window.location.href = url;
            });
    };

    window.history.replaceState({ path: window.location.href }, "", window.location.href);

    document.addEventListener("click", function (event) {
        const link = event.target.closest("[data-nav-link]");

        if (!link || event.defaultPrevented || event.button !== 0) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const url = new URL(link.href);

        if (url.origin !== window.location.origin) {
            return;
        }

        event.preventDefault();
        navigateTo(url.href);
    });

    window.addEventListener("popstate", function (event) {
        const url = event.state && event.state.path ? event.state.path : window.location.href;

        navigateTo(url, { pushState: false });
    });
});
