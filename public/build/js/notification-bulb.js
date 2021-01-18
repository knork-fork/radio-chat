var bulb_timeout;

function notification_light(status)
{
    if (status)
    {
        // Clear previous automatic turn off
        clearTimeout(bulb_timeout);

        // Turn on
        document.getElementById("notification_bulb_on").style.display = "inline-block";
        document.getElementById("notification_bulb_off").style.display = "none";

        // Turn off after 1 second
        bulb_timeout = setTimeout(function() { notification_light(false) }, 1000);
    }
    else
    {
        // Turn off
        document.getElementById("notification_bulb_on").style.display = "none";
        document.getElementById("notification_bulb_off").style.display = "inline-block";
    }
}

// Detect arrow keys - testing only!
/*document.onkeyup = checkKey;
function checkKey(e) 
{
    e = e || window.event;

    if (e.keyCode == '37') 
    {
       // left arrow
       notification_light(true);
    }
    else if (e.keyCode == '39') 
    {
       // right arrow
       notification_light(false);
    }
}*/