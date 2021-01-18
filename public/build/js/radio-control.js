// Elements
var frequency_input = document.getElementById("frequency_input");
var scale_arrow = document.getElementById("scale_arrow");
var message_input = document.getElementById("message_input");

function getOffset( el ) 
{
    var _x = 0;
    var _y = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _y += el.offsetTop - el.scrollTop;
        el = el.offsetParent;
    }
    return { top: _y, left: _x };
}

function clamp(frequency, updateInput = false)
{
    var clamped = false

    if (frequency < 87)
    {
        frequency = 87;
        clamped = true;
    }
    else if (frequency > 110)
    {
        frequency = 110;
        clamped = true;
    }

    if (clamped && updateInput)
        frequency_input.value = frequency;
    
    return frequency;
}

function move(set_frequency = null)
{
    if (set_frequency == null)
    {
        var frequency = frequency_input.value;

        // Clamp between 87 and 110
        frequency = clamp(frequency, true);
    }
    else
    {
        // Clamp between 87 and 110
        frequency = clamp(set_frequency, false);

        frequency_input.value = frequency;
    }

    var frequency_rounded = Math.floor(frequency);
    var frequency_decimal = frequency - frequency_rounded;

    var frequency_element = document.getElementById("frq_" + frequency_rounded);
    var next_element = document.getElementById("frq_" + (frequency_rounded + 1));

    // Distance between left screen edge and left edge of number on scale
    var offset = getOffset(frequency_element).left;
    var offset_next = getOffset(next_element).left; 

    // Get offset between two scale numbers
    var diff = Math.round((offset_next - offset) * frequency_decimal);


    // Width of number on scale
    var width = frequency_element.offsetWidth;

    // Scale arrow is 10px wide, subtract 5 from offset
    offset -= 5;

    // Add half of scale number width to offset
    offset += (width / 2);

    offset += diff;

    var pixels = offset + "px"

    scale_arrow.style.left = pixels;
}

function increment(amount)
{
    var frequency = frequency_input.value
    frequency = parseFloat(frequency) + parseFloat(amount);

    // Hard-round to 4th decimal and then convert back to float to remove following zeroes
    frequency = parseFloat(frequency.toFixed(4));

    // Clamp between 87 and 110
    frequency = clamp(frequency, false);

    frequency_input.value = frequency;
    move(frequency);
}

function instantiate_arrow()
{
    // Move arrow to default location
    move(87);

    // Show arrow
    scale_arrow.style.display = "block";
}

$(document).ready(function() { instantiate_arrow(); });

// Detect arrow keys
document.onkeydown = checkKey;
function checkKey(e) 
{
    // Don't update radio frequency if text input is in focus
    if (document.activeElement == frequency_input || document.activeElement == message_input)
        return;

    e = e || window.event;

    if (e.keyCode == '37') 
    {
       // left arrow
       increment(-0.1);
    }
    else if (e.keyCode == '38') 
    {
       // up arrow
       increment(1);
    }
    else if (e.keyCode == '39') 
    {
       // right arrow
       increment(0.1);
    }
    else if (e.keyCode == '40') 
    {
       // down arrow
       increment(-1);
    }
}

// Detect 'enter' key in message input
message_input.addEventListener("keyup", function(event) 
{
    if (event.key == "Enter") 
    {
        radioSend();
    }
}); 