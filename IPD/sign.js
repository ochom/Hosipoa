var canvas, ctx;
var mouseDown = 0, lastX, lastY;



function draw(ctx,x,y) {
    var size = 3;

  ctx.lineWidth = size;
  ctx.beginPath();
  ctx.moveTo(lastX,lastY);
  ctx.lineTo(x,y);
  ctx.closePath();
  ctx.stroke();

   usbale_sign = true;
}

function clearCanvas(canvas,ctx) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  usbale_sign = false;
  $('#patient_sign').prop('src','');
}

function onMouseDown(e) {
  var xy = getMousePos(e);
  lastX = xy.mouseX;
  lastY = xy.mouseY;
  mouseDown = 1;
}

function onMouseUp() {
  mouseDown = 0
}

function onMouseMove(e) {
  if (mouseDown == 1) {
      var xy = getMousePos(e);
      draw(ctx, xy.mouseX, xy.mouseY);
      lastX = xy.mouseX, lastY = xy.mouseY;
  }
}

function getMousePos(e) {
    var o = {};
  if (!e)
      var e = event
  if (e.offsetX) {
      o.mouseX = e.offsetX
      o.mouseY = e.offsetY
  }
  else if (e.layerX) {
      o.mouseX = e.layerX
      o.mouseY = e.layerY
  }
  return o;
 }

    // Draw something when a touch start is detected
    function touchStart() {
        getTouchPos();

        draw(ctx,touchX,touchY);
        lastX = touchX, lastY = touchY;
        event.preventDefault();
    }

    function touchMove(e) { 
        getTouchPos(e);
        draw(ctx,touchX,touchY);
        lastX = touchX, lastY = touchY;
        event.preventDefault();
    }

    // Get the touch position relative to the top-left of the canvas
    // When we get the raw values of pageX and pageY below, they take into account the scrolling on the page
    // but not the position relative to our target div. We'll adjust them using "target.offsetLeft" and
    // "target.offsetTop" to get the correct values in relation to the top left of the canvas.
    function getTouchPos(e) {
        if (!e)
            var e = event;

        if(e.touches) {
            if (e.touches.length == 1) { // Only deal with one finger
                var touch = e.touches[0]; // Get the information for finger #1
                touchX=touch.pageX-touch.target.offsetLeft;
                touchY=touch.pageY-touch.target.offsetTop;
            }
        }
    }

function init() {
    canvas = document.getElementById('sketchpad')
    ctx = canvas.getContext('2d')
    canvas.addEventListener('mousedown', onMouseDown, false)
    canvas.addEventListener('mousemove', onMouseMove, false)
    canvas.addEventListener('mouseup', onMouseUp, false)

    // React to touch events on the canvas
    canvas.addEventListener('touchstart', touchStart, false);
    canvas.addEventListener('touchmove', touchMove, false);
}
init();