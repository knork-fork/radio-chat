// Channels for subscription
var radio_channel = "radio_main";
var user_channel = "user_" + user_id;



// Prevent trying to subscribe again if reconnecting
var subscribed = false;

// Elements
var connection_indicator_on = document.getElementById("connection_indicator_on");
var connection_indicator_off = document.getElementById("connection_indicator_off");
var notifs_button = document.getElementById("notifs_button");
var notification_list = document.getElementById("notification_list");
var message_list = document.getElementById("messages");
var frequency_input = document.getElementById("frequency_input");
var message_input = document.getElementById("message_input");

// Display a friendly welcome message
welcomeMessage();

// Connect to socket
var socketRedis = new SocketRedis(window.location.origin + ":8080");
socketRedis.onopen = function() 
{
    if (!subscribed)
    {
        // Subscribe to main channel
        socketRedis.subscribe(radio_channel, null, null, function(event, data) {
            radioReceive(event, data);
        });

        // Subscribe to user channel
        socketRedis.subscribe(user_channel, null, null, function(event, data) {
            userReceive(event, data);
        });

        subscribed = true;
    }

    connectionIndicator(true);
};
socketRedis.open();

// Loop websocket connection test to update connection indicator
setInterval(function() { 
    connectionIndicator(socketRedis.isOpen());
}, 5000);

function connectionIndicator(state)
{
    if (state)
    {
        connection_indicator_on.style.display = "inline";
        connection_indicator_off.style.display = "none";
    }
    else
    {
        connection_indicator_on.style.display = "none";
        connection_indicator_off.style.display = "inline";
    }
}

function createElementFromHTML(htmlString) 
{
    var div = document.createElement("div");
    div.innerHTML = htmlString.trim();
  
    return div.firstChild; 
}

function welcomeMessage()
{
    // Create a new message element
    var html = "<div class=\"message\">• "
        + "<span class=\"message_name\">System: </span>"
        + "Welcome to RadioChat!<br>&ensp;Pick your frequency and start exploring!"
        + "<br><br>&ensp;Please make sure the 'ON AIR' indicator is turned on before continuing.</div>";
    var el = createElementFromHTML(html);

    message_list.insertBefore(el, message_list.firstChild);

    // Auto-delete after 30 seconds
    setTimeout(function() { el.remove(); }, 30000);
}

function radioReceive(event, data)
{
    console.log("Data: ", data, ", event: ", event);

    if (event == "message")
    {
        notification_light(true);

        var frequency = document.getElementById("frequency_input").value;
        var unique_hash = data["unique_hash"];
        var url = "/message/get";

        var data = new FormData();
        data.append("frequency", frequency);
        data.append("unique_hash", unique_hash);

        // Retrieve message from backend
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open( "POST", url, true ); // true for asynchronous request
        xmlHttp.onload = function (e) 
        {
            // Check if request is ok
            if (xmlHttp.readyState === 4) 
            {
                if (xmlHttp.status === 200) 
                {
                    var jsonData = JSON.parse(xmlHttp.response);

                    if (jsonData["message"] != "")
                    {
                        var message = jsonData["message"];
                        var corruption = jsonData["corruption"];
                        var author = jsonData["author_name"];
                        
                        // Display only non-empty messages, obviously
                        if (message != "")
                        {
                            // Create a new message element
                            var html = "<div class=\"message\" title=\"Corruption: " + corruption + "\">• ";
                            if (author == user_name)
                                html += "<span class=\"message_name_author\" title=\"This is your account!\">(me): </span>";
                            else if (author != "")
                                html += "<span class=\"message_name\">" + author + ": </span>";
                            html += message + "</div>";
                            var el = createElementFromHTML(html);

                            message_list.insertBefore(el, message_list.firstChild);

                            // Auto-delete after 30 seconds
                            setTimeout(function() { el.remove(); }, 30000);
                        }
                    }
                }
            }
        }
        xmlHttp.send(data);
    }
}

function radioSend()
{
    var frequency = frequency_input.value;
    var message = message_input.value;

    if (frequency == "" || message == "")
        return;

    var url = "/message/send";

    var data = new FormData();
    data.append("frequency", frequency);
    data.append("message", message);

    // Send message to backend
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "POST", url, true ); // true for asynchronous request
    xmlHttp.onload = function (e) 
    {
        // Check if request is ok
        if (xmlHttp.readyState === 4) 
        {
            if (xmlHttp.status === 200) 
            {
                // Empty message input
                message_input.value = "";

                /*
                This message will be added to chat via /message/get request.
                Backend will ping redis subscribers sooner than request response will be received, so it is
                not possible to prevent 'double-adding' of sent message without compromising security and
                adding sender's ID to published data.
                As a side effect, this at least provides user with useful feedback as when the message finally
                appears in chat screen it can be assumed that the message is 100% properly sent and received
                by the server and other users.
                */
            }
        }
    }
    xmlHttp.send(data);
}

function userReceive(event, data)
{
    console.log("Data: ", data, ", event: ", event);

    if (event == "ping" ||true)
    {
        notification_light(true);

        // Display a plus sign on notifs button if notifs sidebar is not open
        if (!notifs)
            notifs_button.innerHTML = "NOTIFS <span style=\"color:red\">(+)</span>";
        
        // Create a new notification element
        var html = "<div class=\"notification\">"
            + "<span class=\"notification_name\">" + data.name + "</span>"
            + " wants to meet you at "
            + "<span class=\"notification_frequency\" onclick=\"move(" + data.frequency + ")\">" + data.frequency + "</span> MHz"
            + "</div>";
        var el = createElementFromHTML(html);

        notification_list.insertBefore(el, notification_list.firstChild);

        // Auto-delete after 10 seconds
        setTimeout(function() { el.remove(); }, 10000);
    }
}

function userSend(target_uid, name, frequency)
{
    socketRedis.publish("user_" + target_uid, "ping", {"name": name, "frequency": frequency});
}