function openPopup(sectionId) {
    $("#sectionTitle").text(sectionId);
    $("#sensorList").html("<p>Loading...</p>");

    $.ajax({
        url: "get_active_sensors.php",
        type: "POST",
        data: { section_id: sectionId },
        success: function(response) {
            console.log("Response received:", response); // Debugging
            $("#sensorList").html(response);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error); // Debugging
            $("#sensorList").html("<p>Error loading data.</p>");
        }
    });

    $("#sensorPopup").show();
}


function closePopup() {
    $("#sensorPopup").hide();
}
$(document).on("click", function(event) {
    if (!$(event.target).closest(".popup-content").length && !$(event.target).closest(".section").length) {
        $("#sensorPopup").hide();
    }
});

function toggleSensor(deviceId, sectionId, action) {
    $.ajax({
        url: "update_sensor_assignment.php",
        type: "POST",
        data: { device_id: deviceId, section_id: sectionId, action: action },
        success: function(response) {
            openPopup(sectionId);  // Refresh the sensor list
        }
    });
}
            let inactivityTimer;
            function resetTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(logoutUser, 600000); // 5 minutes = 300,000ms
            }

            function logoutUser() {
                alert("Session expired due to inactivity. You will be logged out.");
                window.location.href = "logout.php";
            }

            // Detect user activity (mouse, keyboard, or touch)
            document.addEventListener("mousemove", resetTimer);
            document.addEventListener("keypress", resetTimer);
            document.addEventListener("touchstart", resetTimer);
            document.addEventListener("click", resetTimer);

            // Start the inactivity timer when the page loads
            resetTimer();

