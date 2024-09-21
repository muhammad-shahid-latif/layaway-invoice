 function integrate_mode(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }


jQuery(document).ready(function(){
    jQuery('#paymentmode_tab').click();
});        

try {
    var jsonData = JSON.parse(yourJsonData); // Replace yourJsonData with the actual JSON data
    // Use the parsed jsonData object here
} catch (error) {
    console.error("Error parsing JSON: " + error.message);
}

        