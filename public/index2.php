<style>
    button { width:100px; height:100px;}
</style>

<script>
function radioSend(frequency, message, userid)
{
    //var frequency = frequency_input.value;
    //var message = message_input.value;

    if (frequency == "" || message == "")
        return;

    var url = "/message/send";

    var data = new FormData();
    data.append("frequency", frequency);
    data.append("message", message);
    data.append("userid", userid);

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
                //alert("Response: " + xmlHttp.responseText);
            }
        }
    }
    xmlHttp.send(data);
}
</script>

<script>
///

var previous_user = 0;
var previous_message = "";
var loop;

function chat() 
{ 
    /*if (type == 1)
    {
        // ivica
    }*/

    var users = [8, 9, 10, 11, 12, 13];
    var messages = [
        "yoooooo",
        "wazuuup",
        "Hiii :)))",
        "Hey everyone!",
        "Hows everyone doing?",
        "Beep boop",
        "hello :)",
        "test 123"
    ];

    var random_user = users[Math.floor((Math.random()*users.length))];
    while (random_user == previous_user)
    {
        random_user = users[Math.floor((Math.random()*users.length))];
    }
    previous_user = random_user;

    var random_message = messages[Math.floor((Math.random()*messages.length))];
    while (random_message == previous_message)
    {
        random_message = messages[Math.floor((Math.random()*messages.length))];
    }
    previous_message = random_message;

    radioSend(87, random_message, random_user);
}

function chat_loop()
{
    loop = setInterval(function(){ chat(); }, 200);
    
    setTimeout(function(){ clearInterval(loop); }, 15000);
}

///
</script>

<button onclick="">--</button>
<button onclick="chat()">spam</button>
<button onclick="chat_loop()">auto-spam</button>