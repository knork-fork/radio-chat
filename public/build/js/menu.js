var friends = false;
var notifs = false;

function display_friends(state)
{
    if (state)
        document.getElementById("sidebar_friends").style.display = "inline-block";
    else
        document.getElementById("sidebar_friends").style.display = "none";
}

function display_notifs(state)
{
    if (state)
        document.getElementById("sidebar_notifs").style.display = "inline-block";
    else
        document.getElementById("sidebar_notifs").style.display = "none";
}

function toggle_friends()
{
    if (friends)
    {
        display_friends(false);
        friends = false;
    }
    else
    {
        display_notifs(false);
        notifs = false;
        display_friends(true);
        friends = true;
    }
}

function toggle_notifs()
{
    if (notifs)
    {
        display_notifs(false);
        notifs = false;
    }
    else
    {
        display_friends(false);
        friends = false;
        display_notifs(true);
        notifs = true;
    }

    // Remove notification from notifs button
    document.getElementById("notifs_button").innerHTML = "NOTIFS";
}

function select_unique_id()
{
    var el = document.getElementById("unique_id");
    var id = el.innerText;

    // Highlight text
    var range = document.createRange();
    range.selectNode(el);
    window.getSelection().addRange(range);

    // Copy text to clipboard
    document.execCommand("copy");
}

function copy_id(id)
{
    // Copy user id to clipboard
    document.oncopy = function(event) {
        event.clipboardData.setData("Text", id);
        event.preventDefault();
    };
    document.execCommand("copy");
    document.oncopy = undefined;

    // Close friends sidebar (UX feature to ease pasting into chat)
    display_friends(false);
    friends = false;
}

function ping_user(user_id)
{
    var frequency = document.getElementById("frequency_input").value;
    
    userSend(user_id, user_name, frequency);
}

function generate_friend_row_html(jsonData)
{
    var html = '<div class="friend row">';

    html += '<button onclick="ping_user(\'' + jsonData["id"] + '\')" class="btn btn-outline-info sidebar_input" type="button" title="Inform user of your current frequency">PING</button>';
    html += '<button onclick="copy_id(\'' + jsonData["unique_user_id"] + '\')" class="btn btn-outline-info sidebar_input" type="button">COPY ID</button>';
    html += '<div class="sidebar_label friend_name" title="' + jsonData["displayname"] + '">' + jsonData["displayname"] + '</div>';

    html += '</div>';

    return html;
}

function add_friend()
{
    var user_unique_id = document.getElementById("unique_id_input").value;
    var url = "/friend/add";

    var data = new FormData();
    data.append('user_unique_id', user_unique_id);

    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "POST", url, true ); // true for asynchronous request

    // Event after request is complete
    xmlHttp.onload = function (e) 
    {
        // Check if request is ok
        if (xmlHttp.readyState === 4) 
        {
            if (xmlHttp.status === 200) 
            {
                jsonData = JSON.parse(xmlHttp.response);

                document.getElementById("unique_id_input").value = "";

                document.getElementById("friend_list").innerHTML += generate_friend_row_html(jsonData);
                
                //alert("Friend added");
            }
            else
                alert("Failed adding friend");
        }
    }

    // Failed request
    xmlHttp.onerror = function (e) { alert("Error!"); };

    // Send request
    xmlHttp.send(data);
}