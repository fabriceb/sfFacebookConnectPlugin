/* Copyright (c) 2008, Marcel Laverdet and Facebook, inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of Facebook, inc. nor the names of its contributors
 *      may be used to endorse or promote products derived from this software
 *      without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. */
function Animation(obj) {
  if (this == window) {
    return new Animation(obj);
  } else {
    this.obj = obj;
    this._reset_state();
    this.queue = [];
    this.last_attr = null;
  }
}
Animation.resolution = 20;
Animation.offset = 0;

// Initializes the state to blank values
Animation.prototype._reset_state = function() {
  this.state = {
    attrs: {},
    duration: 500 // default duration
  }
}

// Stops any current animation
Animation.prototype.stop = function() {
  this._reset_state();
  this.queue = [];
  return this;
}

// Builds an overflow:hidden container for this.obj. Used with .blind()
Animation.prototype._build_container = function() {
  if (this.container_div) {
    this._refresh_container();
    return;
  }
  // ref-counting here on the magic container in case someone decides to start two animations with blind() on the same element
  // Either way it's not ideal because if animating to 'auto' the calculations will probably be incorrect... but at least this
  // way it won't tear up your DOM.
  if (this.obj.firstChild && this.obj.firstChild.__animation_refs) {
    this.container_div = this.obj.firstChild;
    this.container_div.__animation_refs++;
    this._refresh_container();
    return;
  }
  var container = document.createElement('div');
  container.style.padding = '0px';
  container.style.margin = '0px';
  container.style.border = '0px';
  container.__animation_refs = 1;
  var children = this.obj.childNodes;
  while (children.length) {
    container.appendChild(children[0]);
  }
  this.obj.appendChild(container);
  this.obj.style.overflow = 'hidden';
  this.container_div = container;
  this._refresh_container();
}

// Refreshes the size of the container. Used on checkpoints and such.
Animation.prototype._refresh_container = function() {
  this.container_div.style.height = 'auto';
  this.container_div.style.width = 'auto';
  this.container_div.style.height = this.container_div.offsetHeight+'px';
  this.container_div.style.width = this.container_div.offsetWidth+'px';
}

// Destroys the container built by _build_container()
Animation.prototype._destroy_container = function() {
  if (!this.container_div) {
    return;
  }
  if (!--this.container_div.__animation_refs) {
    var children = this.container_div.childNodes;
    while (children.length) {
      this.obj.appendChild(children[0]);
    }
    this.obj.removeChild(this.container_div);
  }
  this.container_div = null;
}

// Generalized attr function. Calls to .to, .by, and .from go through this
Animation.ATTR_TO = 1;
Animation.ATTR_BY = 2;
Animation.ATTR_FROM = 3;
Animation.prototype._attr = function(attr, value, mode) {

  // Turn stuff like border-left into borderLeft
  attr = attr.replace(/-[a-z]/gi, function(l) {
    return l.substring(1).toUpperCase();
  });

  var auto = false;
  switch (attr) {
    case 'background':
      this._attr('backgroundColor', value, mode);
      return this;

    case 'margin':
      value = Animation.parse_group(value);
      this._attr('marginBottom', value[0], mode);
      this._attr('marginLeft', value[1], mode);
      this._attr('marginRight', value[2], mode);
      this._attr('marginTop', value[3], mode);
      return this;

    case 'padding':
      value = Animation.parse_group(value);
      this._attr('paddingBottom', value[0], mode);
      this._attr('paddingLeft', value[1], mode);
      this._attr('paddingRight', value[2], mode);
      this._attr('paddingTop', value[3], mode);
      return this;

    case 'backgroundColor':
    case 'borderColor':
    case 'color':
      value = Animation.parse_color(value);
      break;

    case 'opacity':
      value = parseFloat(value, 10);
      break;

    case 'height':
    case 'width':
      if (value == 'auto') {
        auto = true;
      } else {
        value = parseInt(value, 10);
      }
      break;

    case 'borderWidth':
    case 'lineHeight':
    case 'fontSize':
    case 'marginBottom':
    case 'marginLeft':
    case 'marginRight':
    case 'marginTop':
    case 'paddingBottom':
    case 'paddingLeft':
    case 'paddingRight':
    case 'paddingTop':
    case 'bottom':
    case 'left':
    case 'right':
    case 'top':
    case 'scrollTop':
    case 'scrollLeft':
      value = parseInt(value, 10);
      break;

    default:
      throw new Error(attr+' is not a supported attribute!');
  }

  if (this.state.attrs[attr] === undefined) {
    this.state.attrs[attr] = {};
  }
  if (auto) {
    this.state.attrs[attr].auto = true;
  }
  switch (mode) {
    case Animation.ATTR_FROM:
      this.state.attrs[attr].start = value;
      break;

    case Animation.ATTR_BY:
      this.state.attrs[attr].by = true;
      // fall through

    case Animation.ATTR_TO:
      this.state.attrs[attr].value = value;
      break;
  }
}

// Explcit animation to a certain value
Animation.prototype.to = function(attr, value) {
  if (value === undefined) {
    this._attr(this.last_attr, attr, Animation.ATTR_TO);
  } else {
    this._attr(attr, value, Animation.ATTR_TO);
    this.last_attr = attr;
  }
  return this;
}

// Animation by a value (i.e. add this value to the current value)
Animation.prototype.by = function(attr, value) {
  if (value === undefined) {
    this._attr(this.last_attr, attr, Animation.ATTR_BY);
  } else {
    this._attr(attr, value, Animation.ATTR_BY);
    this.last_attr = attr;
  }
  return this;
}

// Start the animation from a value instead of the current value
Animation.prototype.from = function(attr, value) {
  if (value === undefined) {
    this._attr(this.last_attr, attr, Animation.ATTR_FROM);
  } else {
    this._attr(attr, value, Animation.ATTR_FROM);
    this.last_attr = attr;
  }
  return this;
}

// How long is this animation supposed to last (in miliseconds)
Animation.prototype.duration = function(duration) {
  this.state.duration = duration ? duration : 0;
  return this;
}

// Checkpoint the animation to start a new one.
Animation.prototype.checkpoint = function(distance /* = 1.0 */, callback) {
  if (distance === undefined) {
    distance = 1;
  }
  this.state.checkpoint = distance;
  this.state.checkpointcb = callback;
  this.queue.push(this.state);
  this._reset_state();
  return this;
}

// This animation requires an overflow container (usually used for width animations)
Animation.prototype.blind = function() {
  this.state.blind = true;
  return this;
}

// Hide this object at the end of the animation
Animation.prototype.hide = function() {
  this.state.hide = true;
  return this;
}

// Show this object at the beginning of the animation
Animation.prototype.show = function() {
  this.state.show = true;
  return this;
}

// Use an easing function to adjust the distribution of the animation state over frames
Animation.prototype.ease = function(ease) {
  this.state.ease = ease;
  return this;
}

// Let the animation begin!
Animation.prototype.go = function() {
  var time = (new Date()).getTime();
  this.queue.push(this.state);

  for (var i = 0; i < this.queue.length; i++) {
    this.queue[i].start = time - Animation.offset;
    if (this.queue[i].checkpoint) {
      time += this.queue[i].checkpoint * this.queue[i].duration;
    }
  }
  Animation.push(this);
  return this;
}

// Draw a frame for this animation
Animation.prototype._frame = function(time) {
  var done = true;
  var still_needs_container = false;
  var whacky_firefox = false;
  for (var i = 0; i < this.queue.length; i++) {

    // If this animation shouldn't start yet we can abort early
    var cur = this.queue[i];
    if (cur.start > time) {
      done = false;
      continue;
    } else if (cur.checkpointcb && (cur.checkpoint * cur.duration + cur.start > time)) {
      this._callback(cur.checkpointcb, time - cur.start - cur.checkpoint * cur.duration);
      cur.checkpointcb = null;
    }

    // We need to initialize an animation on the first frame
    if (cur.started === undefined) {
      if (cur.show) {
        this.obj.style.display = 'block';
      }
      for (var a in cur.attrs) {
        if (cur.attrs[a].start !== undefined) {
          continue;
        }
        switch (a) {
          case 'backgroundColor':
          case 'borderColor':
          case 'color':
            // Defer to the left border color, whatever.
            var val = Animation.parse_color(Animation.get_style(this.obj, a == 'borderColor' ? 'borderLeftColor' : a));

            // I'm not sure why anyone would want to do relative color adjustment... but at least they can
            if (cur.attrs[a].by) {
              cur.attrs[a].value[0] = Math.min(255, Math.max(0, cur.attrs[a].value[0] + val[0]));
              cur.attrs[a].value[1] = Math.min(255, Math.max(0, cur.attrs[a].value[1] + val[1]));
              cur.attrs[a].value[2] = Math.min(255, Math.max(0, cur.attrs[a].value[2] + val[2]));
            }
            break;

          case 'opacity':
            var val = ((val = Animation.get_style(this.obj, 'opacity')) && parseFloat(val)) ||
                      ((val = Animation.get_style(this.obj, 'opacity')) && (val = /(\d+(?:\.\d+)?)/.exec(val)) && parseFloat(val.pop()) / 100) ||
                      1;
            if (cur.attrs[a].by) {
              cur.attrs[a].value = Math.min(1, Math.max(0, cur.attrs[a].value + val));
            }
            break;

          case 'height':
          case 'width':
            var val = Animation['get_'+a](this.obj);
            if (cur.attrs[a].by) {
              cur.attrs[a].value += val;
            }
            break;

          case 'scrollLeft':
          case 'scrollTop':
            var val = (this.obj == document.body) ? (document.documentElement[a] || document.body[a]) : this.obj[a];
            if (cur.attrs[a].by) {
              cur.attrs[a].value += val;
            }
            cur['last'+a] = val;
            break;

          default:
            var val = parseInt(Animation.get_style(this.obj, a), 10);
            if (cur.attrs[a].by) {
              cur.attrs[a].value += val;
            }
            break;
        }
        cur.attrs[a].start = val;
      }

      // If we're animating height or width to "auto" we need to do some DOM-fu to figure out what that means in px
      if ((cur.attrs.height && cur.attrs.height.auto) ||
          (cur.attrs.width && cur.attrs.width.auto)) {

        // This is a silly fix for Firefox's whacky redrawing. This bug can be reproduced with the following code:
        /* <div style="width: 300px; height: 300px; background: red" id="test"></div>
           <script>
             setInterval(function() {
               var obj = document.getElementById('test');
           //    var p = obj.parentNode;
               obj.style.visibility = 'hidden';
               obj.offsetWidth; // request a property, requires a re-render
           //    p.removeChild(obj);
               obj.style.visibility = 'visible';
           //    p.appendChild(obj);
             }, 100);
           </script> */
        // In all browsers that aren't Firefox the div will stay solid red. In Firefox it flickers. The workaround
        // for the bug is included in the code block above, but commented out.
        if (/Firefox\/[12]\./.test(navigator.userAgent)) {
          whacky_firefox = true;
        }

        // Set any attributes that affect element size to their final desired values
        this._destroy_container();
        for (var a in {height: 1, width: 1,
                       fontSize: 1,
                       borderLeftWidth: 1, borderRightWidth: 1, borderTopWidth: 1, borderBottomWidth: 1,
                       paddingLeft: 1, paddingRight: 1, paddingTop: 1, paddingBottom: 1}) {
          if (cur.attrs[a]) {
            this.obj.style[a] = cur.attrs[a].value + (typeof cur.attrs[a].value == 'number' ? 'px' : '');
          }
        }

        // Record the dimensions of what the element will look like after the animation
        if (cur.attrs.height && cur.attrs.height.auto) {
          cur.attrs.height.value = Animation.get_height(this.obj);
        }
        if (cur.attrs.width && cur.attrs.width.auto) {
          cur.attrs.width.value = Animation.get_width(this.obj);
        }

        // We don't need to do anything else with temporarily adjusted style because they're
        // about to be overwritten in the frame loop below
      }

      cur.started = true;
      if (cur.blind) {
        this._build_container();
      }
    }

    // Calculate the animation's progress from 0 - 1
    var p = (time - cur.start) / cur.duration;
    if (p >= 1) {
      p = 1;
      if (cur.hide) {
        this.obj.style.display = 'none';
      }
    } else {
      done = false;
    }
    var pc = cur.ease ? cur.ease(p) : p;

    // If this needs a blind container and doesn't have one, we build it
    if (!still_needs_container && p != 1 && cur.blind) {
      still_needs_container = true;
    }

    // Hack documented above
    if (whacky_firefox && this.obj.parentNode) {
      var parentNode = this.obj.parentNode;
      var nextChild = this.obj.nextSibling;
      parentNode.removeChild(this.obj);
    }

    // Loop through each animated attribute and set it where it needs to be
    for (var a in cur.attrs) {
      switch (a) {
        case 'backgroundColor':
        case 'borderColor':
        case 'color':
          this.obj.style[a] = 'rgb('+
            Animation.calc_tween(pc, cur.attrs[a].start[0], cur.attrs[a].value[0], true)+','+
            Animation.calc_tween(pc, cur.attrs[a].start[1], cur.attrs[a].value[1], true)+','+
            Animation.calc_tween(pc, cur.attrs[a].start[2], cur.attrs[a].value[2], true)+')';
          break;

        case 'opacity':
          var opacity = Animation.calc_tween(pc, cur.attrs[a].start, cur.attrs[a].value);
          try {
            this.obj.style.opacity = (opacity == 1 ? '' : opacity);
            this.obj.style.filter = (opacity == 1 ? '' : 'alpha(opacity=' + opacity * 100 + ')');
          }
          catch (e) {}
          break;

        case 'height':
        case 'width':
          this.obj.style[a] = pc == 1 && cur.attrs[a].auto ? 'auto' :
                              Animation.calc_tween(pc, cur.attrs[a].start, cur.attrs[a].value, true)+'px';
          break;

        case 'scrollLeft':
        case 'scrollTop':
          // Special-case here for scrolling. If the user overrides the scroll we immediately terminate this animation
          var val = (this.obj == document.body) ? (document.documentElement[a] || document.body[a]) : this.obj[a];
          if (cur['last'+a] != val) {
            delete cur.attrs[a];
          } else {
            var diff = Animation.calc_tween(pc, cur.attrs[a].start, cur.attrs[a].value, true) - val;
            if (a == 'scrollLeft') {
              window.scrollBy(diff, 0);
            } else {
              window.scrollBy(0, diff);
            }
            cur['last'+a] = diff + val;
          }
          break;

        default:
          this.obj.style[a] = Animation.calc_tween(pc, cur.attrs[a].start, cur.attrs[a].value, true)+'px';
          break;
      }
    }

    // If this animation is complete remove it from the queue
    if (p == 1) {
      this.queue.splice(i--, 1);
      this._callback(cur.ondone, time - cur.start - cur.duration);
    }
  }

  // Hack documented above
  if (whacky_firefox) {
    parentNode[nextChild ? 'insertBefore' : 'appendChild'](this.obj, nextChild);
  }

  if (!still_needs_container && this.container_div) {
    this._destroy_container();
  }
  return !done;
}

// Add a callback to fire when this animation is finished
Animation.prototype.ondone = function(fn) {
  this.state.ondone = fn;
  return this;
}

// Call a callback with a time offset (for instantiating more animations)
Animation.prototype._callback = function(callback, offset) {
  if (callback) {
    Animation.offset = offset;
    callback.call(this);
    Animation.offset = 0;
  }
}

// Calculates a value in between two values based on a percentage. Basically a weighted average.
Animation.calc_tween = function(p, v1, v2, whole) {
  return (whole ? parseInt : parseFloat)((v2 - v1) * p + v1, 10);
}

// Takes a color like #fff and returns an array of [255, 255, 255].
Animation.parse_color = function(color) {
  var hex = /^#([a-f0-9]{1,2})([a-f0-9]{1,2})([a-f0-9]{1,2})$/i.exec(color);
  if (hex) {
    return [parseInt(hex[1].length == 1 ? hex[1] + hex[1] : hex[1], 16),
            parseInt(hex[2].length == 1 ? hex[2] + hex[2] : hex[2], 16),
            parseInt(hex[3].length == 1 ? hex[3] + hex[3] : hex[3], 16)];
  } else {
    var rgb = /^rgba? *\(([0-9]+), *([0-9]+), *([0-9]+)(?:, *([0-9]+))?\)$/.exec(color);
    if (rgb) {
      if (rgb[4] === '0') {
        return [255, 255, 255]; // transparent
      } else {
        return [parseInt(rgb[1], 10), parseInt(rgb[2], 10), parseInt(rgb[3], 10)];
      }
    } else if (color == 'transparent') {
      return [255, 255, 255]; // not much we can do here...
    } else {
      // When we open this to Platform we'll need a key-value list of names to rgb values
      throw 'Named color attributes are not supported.';
    }
  }
}

// Takes a CSS attribute like padding or margin and returns an explicit array of 4 values
// Ex: '0px 1px' -> ['0px', '1px', '0px', '1px']
Animation.parse_group = function(value) {
  var value = trim(value).split(/ +/);
  if (value.length == 4) {
    return value;
  } else if (value.length == 3) {
    return [value[0], value[1], value[2], value[1]];
  } else if (value.length == 2) {
    return [value[0], value[1], value[0], value[1]];
  } else {
    return [value[0], value[0], value[0], value[0]];
  }
}

// Gets the current height of an element which when used with obj.style.height = height+'px' is a visual NO-OP
Animation.get_height = function(obj) {
  var pT = parseInt(Animation.get_style(obj, 'paddingTop'), 10),
      pB = parseInt(Animation.get_style(obj, 'paddingBottom'), 10),
      bT = parseInt(Animation.get_style(obj, 'borderTopWidth'), 10),
      bW = parseInt(Animation.get_style(obj, 'borderBottomWidth'), 10);
  return obj.offsetHeight - (pT ? pT : 0) - (pB ? pB : 0) - (bT ? bT : 0) - (bW ? bW : 0);
}

// Similar to get_height except for widths
Animation.get_width = function(obj) {
  var pL = parseInt(Animation.get_style(obj, 'paddingLeft'), 10),
      pR = parseInt(Animation.get_style(obj, 'paddingRight'), 10),
      bL = parseInt(Animation.get_style(obj, 'borderLeftWidth'), 10),
      bR = parseInt(Animation.get_style(obj, 'borderRightWidth'), 10);
  return obj.offsetWidth - (pL ? pL : 0) - (pR ? pR : 0) - (bL ? bL : 0) - (bR ? bR : 0);
}

// Gets the computed style of an element
Animation.get_style = function(obj, prop) {
  var temp;
  return (window.getComputedStyle && window.getComputedStyle(obj, null).getPropertyValue(prop.replace(/[A-Z]/g, function(match) { return '-' + match.toLowerCase() }))) ||
         (document.defaultView && document.defaultView.getComputedStyle && (temp = document.defaultView.getComputedStyle(obj, null)) && temp.getPropertyValue(prop.replace(/[A-Z]/g, function(match) { return '-' + match.toLowerCase() }))) ||
         (obj.currentStyle && obj.currentStyle[prop]) ||
         obj.style[prop];
}

// Add this animation object to the global animation stack.
Animation.push = function(instance) {
  if (!Animation.active) {
    Animation.active = [];
  }
  Animation.active.push(instance);
  if (!Animation.timeout) {
    Animation.timeout = setInterval(Animation.animate, Animation.resolution);
  }
}

// Renders a frame from each naimation currently active. By putting all our animations in one
// stack it gives us the advantage of a single setInterval with all style updates in a single
// callback. That means the browser will do less rendering and multiple animations will be
// smoother.
Animation.animate = function() {
  var done = true;
  var time = (new Date()).getTime();
  for (var i = 0; i < Animation.active.length; i++) {
    if (Animation.active[i]._frame(time)) {
      done = false;
    } else {
      Animation.active.splice(i--, 1); // remove from the list
    }
  }
  if (done) {
    clearInterval(Animation.timeout);
    Animation.timeout = null;
  }
}

// Ease functions. These functions all have a domain and (maybe) range of 0 - 1
Animation.ease = {}
Animation.ease.begin = function(p) {
  return p * p;
}
Animation.ease.end = function(p) {
  p -= 1;
  return -(p * p) + 1;
}
Animation.ease.both = function(p) {
  if (p <= 0.5) {
    return (p * p) * 2;
  } else {
    p -= 1;
    return (p * p) * -2 + 1;
  }
}
