function toggleNav() {
    var nav = document.getElementById("sideNav");
    var body = document.body;

    if (nav.style.width === "250px") {
        nav.style.width = "0";
        body.classList.remove("nav-open"); // Remove the class when closed
    } else {
        nav.style.width = "250px";
        body.classList.add("nav-open"); // Add the class when opened
    }
}

function loadContent(page) {
    // Fetch the content from the selected page
    fetch(page)
        .then(response => response.text())
        .then(data => {
            // Inject the content into the content div
            document.getElementById('content').innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading content:', error);
        });

    // Close the side navigation after loading content
    toggleNav();
}

