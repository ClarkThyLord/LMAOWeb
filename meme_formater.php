<!DOCTYPE html>
<html>
	<head>
	  <title>LMAOBot</title>
	  <meta charset="UTF-8">
	  <meta name="viewport" content="width=device-width, initial-scale=1">
	  <meta name="description" content="LMAOBot is an innovative Discord bot equipped with a dank soundboard. You can use Discord's chat function to give lmaobot commands, to which the bot sends a response. lmaobot uses the lmao prefix.">
	  <link rel="stylesheet" href="./css/libs/w3.css">
	  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
	  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	  <link rel="shortcut icon" type="image/x-icon" href="./assets/LMAO_logo.jpg"/>

		<style>
			body {
				font-family: "Lato", sans-serif
			}

			canvas {
				box-shadow: rgb(0, 0, 0) 0px 0px 10px;
			}

			.smallScreenNav {
				display: none
			}

			.canvas-container {
				margin: 1%;
				display: block;
		    margin-left: auto;
		    margin-right: auto;
			}

			.toolbar {
				margin: 1%;
			}

			.toolbar input, .toolbar select {
				margin: 2.5px;
				padding: 5px;
				border-radius: 10px;
			}
		</style>
	</head>
	<body>

	  <!-- Navbar -->
	  <div class="w3-top">
	    <div class="w3-bar w3-black w3-card">
	      <a class="w3-bar-item w3-button w3-padding-large w3-hide-medium w3-hide-large w3-right" href="javascript:void(0)" onclick="setup_navbar()" title="Toggle Navigation Menu"><i class="fa fa-bars"></i></a>
	      <a href="index.php" class="w3-bar-item w3-button w3-padding-large">LMAOBOT</a>
	      <a href="#" class="w3-bar-item w3-button w3-padding-large w3-hide-small w3-right">MEME FORMATER</a>
	      <a href="index.php#commands" class="w3-bar-item w3-button w3-padding-large w3-hide-small w3-right">COMMANDS</a>
	      <a href="index.php#invite" class="w3-bar-item w3-button w3-padding-large w3-hide-small w3-right">INVITE</a>
	    </div>
	  </div>

	  <!-- Navbar on small screens -->
	  <div id="smallScreenNav" class="w3-bar-block w3-black w3-hide w3-hide-large w3-hide-medium w3-top" style="margin-top:46px">
	    <a href="index.php#invite" class="w3-bar-item w3-button w3-padding-large">INVITE</a>
	    <a href="index.php#commands" class="w3-bar-item w3-button w3-padding-large">COMMANDS</a>
	    <a href="#" class="w3-bar-item w3-button w3-padding-large">MEME FORMATER</a>
	  </div>

	  <!-- Start Page content -->
	  <div class="w3-content" style="max-width: 2000px; margin-top: 46px; text-align: center; align-content: center;">

			<canvas style="margin: 5px;" width="800" height="600" id="master_canvas"><b>HTML Canvas isn't supported...</b></canvas>

			<form class="toolbar">
				<input type="file" onchange="load_background(this.files[0]);" accept=".jpg, .png" style="display: none;" name="image_file" />
				<input type="button" onclick="image_file.click();" value="Open image..." />
				<br />
				<select name="add_type">
					<option value="rectangle">
						Square
					</option>
					<option value="circle">
						Circle
					</option>
				</select>
				<br />
				<input type="button" onclick="new new_step({type: 'text', shape: add_type.value});" value="Add Text" name="add_text" />
				<input type="button" onclick="new new_step({type: 'image', shape: add_type.value});" value="Add Image" name="add_image" />
				<br />
				<input type="button" onclick="export_format();" value="Export" name="export" />
				<input type="file" onchange="import_format(this.files[0]);" style="display: none;" name="format_file" />
				<input type="button" value="Import" onclick="format_file.click();" name="import" />
			</form>

		</div>
		<!-- End Page Content -->

	<!-- Footer -->
	<footer class="w3-container w3-padding-64 w3-center w3-opacity w3-light-grey w3-xlarge">
	  <p class="w3-medium">&copy; LMAOBOT ALL RIGHTS RESERVED. <?php echo date("Y"); ?></p>
	</footer>

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-117753493-1"></script>

	<script>
		// Used to toggle the menu on small screens when clicking on the menu button
		function setup_navbar() {
			var x = document.getElementById("smallScreenNav");
			if (x.className.indexOf("w3-show") == -1) {
				x.className += " w3-show";
			} else {
				x.className = x.className.replace(" w3-show", "");
			}
		}
	</script>

	<script src="./js/libs/fabric.min.js"></script>
	<script>
		// Setup global variables
		var VERSION = 1.0;
		var canvas;
		var meme = {
			name: '',
			data: undefined
		};
		var steps = [];

		// Setup Fabric.js on page load
		window.onload = function () {
			// Setup Canvas
			canvas = new fabric.Canvas('master_canvas');
			canvas.setBackgroundColor('black', canvas.renderAll.bind(canvas));

			canvas.on({
				// Desktop/Keyboard/Mouse events
				'mouse:down': function(opt) {
				  if (opt.e.ctrlKey) {
				    this.isDragging = true;
				    this.selection = false;

				    this.lastPosX = opt.e.clientX;
				    this.lastPosY = opt.e.clientY;
				  }
				},
				'mouse:move': function(opt) {
				  if (this.isDragging) {
				    this.viewportTransform[4] += opt.e.clientX - this.lastPosX;
				    this.viewportTransform[5] += opt.e.clientY - this.lastPosY;

				    canvas.requestRenderAll();

				    this.lastPosX = opt.e.clientX;
				    this.lastPosY = opt.e.clientY;
				  }
				},
				'mouse:up': function(opt) {
				  this.isDragging = false;
				  this.selection = true;
				},
				'mouse:wheel': function(opt) {
				  var delta = opt.e.deltaY;
				  var pointer = canvas.getPointer(opt.e);

				  // Zoom amount
				  var zoom;
				  if (opt.e.ctrlKey) {
				    zoom = canvas.getZoom() - delta / 25;
				  } else {
				    zoom = canvas.getZoom() - delta / 75;
				  }

				  // Limit zoom
				  zoom = Math.min(20, Math.max(0.01, zoom));

				  canvas.zoomToPoint({
				    x: opt.e.offsetX,
				    y: opt.e.offsetY
				  }, zoom);

				  opt.e.preventDefault();
				  opt.e.stopPropagation();
				},
				// Mobile/Touch events
				'touch:gesture': function(opt) {
				  if (opt.e.touches && opt.e.touches.length == 2) {
				    pausePanning = true;
				    var point = new fabric.Point(opt.self.x, opt.self.y);
				    if (opt.self.state == "start") {
				      zoomStartScale = self.canvas.getZoom();
				    }
				    var delta = zoomStartScale * opt.self.scale;
				    self.canvas.zoomToPoint(point, delta);
				    pausePanning = false;
				  }
				},
				'object:selected': function() {
				  pausePanning = true;
				},
				'selection:cleared': function() {
				  pausePanning = false;
				},
				'touch:drag': function(opt) {
				  if (pausePanning == false && undefined != opt.e.layerX && undefined != opt.e.layerY) {
				    currentX = opt.e.layerX;
				    currentY = opt.e.layerY;
				    xChange = currentX - lastX;
				    yChange = currentY - lastY;

				    if ((Math.abs(currentX - lastX) <= 50) && (Math.abs(currentY - lastY) <= 50)) {
				      var delta = new fabric.Point(xChange, yChange);
				      canvas.relativePan(delta);
				    }

				    lastX = opt.e.layerX;
				    lastY = opt.e.layerY;
				  }
				}
			});

		  // Setup resize
		  window.addEventListener("resize", resize_canvas);
		  resize_canvas();
		}


		/**
		 * Exports current Steps into a format.
		 * @return {undefined} Returns nothing.
		 */
		function export_format() {
			if (meme.data === undefined) return alert('You haven\'t loaded a image!');

			var format = {
				version: VERSION,
				image: meme.data,
				steps: []
			}
			for (var step in steps) {
				format.steps.push(steps[step].export());
			}

		  // Setting up a DOM is necessary for downloads in some Browsers
		  var download = document.createElement('a');
		  download.setAttribute('href', 'data:text/JSON,' + JSON.stringify(format));
		  download.setAttribute('download', meme.name + '.json');
		  document.body.appendChild(download);
		  download.click();
		  download.remove();
		}


		/**
		 * Imports Steps from a format.
		 * @param {File} [format_file] File that's a format; .json.
		 * @return {undefined} Returns nothing.
		 */
		function import_format(format_file) {
			meme.name = format_file.name.split(".");
			var reader = new FileReader();

			reader.onload = function(event) {
			  var format = JSON.parse(event.target.result);

			  if (format.version === VERSION) {
			    load_background(format.image);

					clear_steps();
					for (var step in format.steps) {
						new new_step(format.steps[step]);
					}
			  } else alert('Given format isn\'t up to date!\nGiven:' + format.version + '\nRequired:' + VERSION);
			};

			reader.readAsText(format_file);
		}


		/**
		 * Generate a random RGB color.
		 * @return {Array} Returns a Array, containing three integers, representing a random RGB color.
		 */
		function randomRGB() {
		  return [Math.floor(Math.random() * 255), Math.floor(Math.random() * 255), Math.floor(Math.random() * 255)];
		}


		/**
		 * Given a RGB object return a RGB string.
		 * @param {Array} [rgb] Array representing RGB; [ Red, Green, Blue ].
		 * @return {String} Returns a string representing a RGB color.
		 */
		function stringRGB(rgb) {
		  return 'rgb(' + Math.floor(rgb[0]) + ',' + Math.floor(rgb[1]) + ',' + Math.floor(rgb[2]) + ')';
		}


		/**
		 * Given a RGB object and alpha return a RGBA string.
		 * @param {Array} [rgb] Array representing RGB; [ Red, Green, Blue ].
		 * @param {Integer} [alpha] Alpha amount; Range: 0 - 1.
		 * @return {String} Returns a string representing a RGBA color.
		 */
		function stringRGBA(rgb, alpha) {
		  // Make sure alpha is valid
		  if (alpha == undefined) {
		    alpha = 1;
		  } else if (alpha < 0) {
		    alpha = 0;
		  } else if (alpha > 1) {
		    alpha = 1;
		  }

		  return 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + alpha + ')';
		}


		/**
		 * Resize Canvas and it's content.
		 * @return {undefined} Returns nothing.
		 */
		function resize_canvas() {
		  // Resize Canvas
			canvas.setWidth(window.innerWidth - 100);
			canvas.setHeight(window.innerHeight - 100);

			// Fix background image for Canvas
			if (canvas.backgroundImage !== null) {
				canvas.backgroundImage.left = canvas.width / 2;
				canvas.backgroundImage.top = canvas.height / 2;
				if (canvas.width < canvas.height * 1.30) {
					canvas.backgroundImage.scaleToWidth(canvas.width * 0.75);
				} else {
					canvas.backgroundImage.scaleToHeight(canvas.height * 0.75);
				}
			}

			// Fix Canvas's content
			canvas.calcOffset();

			canvas.requestRenderAll();
		}


		/**
		 * Load background for the Canvas.
		 * @param {String/Object} [image] A String or Object representing a File.
		 * @return {undefined} Returns nothing.
		 */
		 function load_background(image) {
			if (typeof image === 'string') {
 				 // Setup Meme info
				 // TODO Setup meme name
 				 // meme.name = image_file.name;
 				 meme.data = image;

 				 new fabric.Image.fromURL(image, function (image) {
 					 // Set Image to center
 					 image.originX = image.originY = 'center';
 					 image.left = canvas.width / 2;
 					 image.top = canvas.height / 2;

 					 // Resize Image to fit Canvas
 					 if (canvas.width < canvas.height * 1.30) {
 						 image.scaleToWidth(canvas.width * 0.75);
 					 } else {
 						 image.scaleToHeight(canvas.height * 0.75);
 					 }

 					 // Set Canvas background
 					 canvas.setBackgroundImage(image);
 					 canvas.requestRenderAll();
 				 });
			} else if (typeof image === 'object') {
  		 	var reader  = new FileReader();

  			// Once file has been read do the following
  			reader.addEventListener('load', function () {
  				// Setup Meme info
  				meme.name = image.name;
  				meme.data = reader.result.replace(/^data:image\/(png|jpg);base64,/, "");

  				new fabric.Image.fromURL(reader.result, function (image_obj) {
  					// Set Image to center
  					image_obj.originX = image_obj.originY = 'center';
  					image_obj.left = canvas.width / 2;
  					image_obj.top = canvas.height / 2;

  					// Resize Image to fit Canvas
  					if (canvas.width < canvas.height * 1.30) {
  						image_obj.scaleToWidth(canvas.width * 0.75);
  					} else {
  						image_obj.scaleToHeight(canvas.height * 0.75);
  					}

  					// Set Canvas background
  					canvas.setBackgroundImage(image_obj);
  					canvas.requestRenderAll();
  				});
  			});

  			// Read image if any given
  			if (image) {
  				reader.readAsDataURL(image)
  			}
			}
		 }


 		/**
 		 * Clear all Steps.
 		 * @return {undefined} Returns nothing.
 		 */
 		 function clear_steps() {
			 for (var step in steps) {
				 // Remove Step's content from Canvas
				 canvas.remove(steps[step].content);
				 canvas.requestRenderAll();
			 }
			 steps = [];
		 }


 		/**
 		 * Create a Step in Canvas.
 		 * @param {Object} [options] Options for step; options:type, shape, positon, size and etc.
 		 * @return {Object} Returns instance of this new Step.
 		 */
 		 function new_step(options) {
			options = Object.assign({
				type: 'text',
				shape: 'rectangle',
				color: randomRGB(),
				angle: 0,
				size: [
					100,
					100
				],
				position: [
					canvas.width / 2,
					canvas.height / 2
				]
			}, options);

 			this.id = steps.push(this) - 1;
			this.type = options.type;
			this.shape = options.shape;
			this.color = options.color;

			this.content = new fabric.Group([], {
				lockScalingFlip: true,
				originX: 'center',
				originY: 'center',
				width: options.size[0],
				height: options.size[1],
				angle: options.angle,
				fill: 'rgba(0, 0, 0, 0)',
				backgroundColor: 'rgba(0, 0, 0, 0)'
			});

			if (this.shape === 'rectangle') {
				this.content.addWithUpdate(new fabric.Rect({
					originX: 'center',
					originY: 'center',
					width: 100,
					height: 100,
					stroke: stringRGB(this.color),
					strokeWidth: 3,
					fill: 'rgba(0, 0, 0, 0)',
					backgroundColor: 'rgba(0, 0, 0, 0)'
				}));
			} else if (this.shape === 'circle') {
				this.content.addWithUpdate(new fabric.Circle({
					originX: 'center',
					originY: 'center',
					radius: 50,
					stroke: stringRGB(this.color),
					strokeWidth: 3,
					fill: 'rgba(0, 0, 0, 0)',
					backgroundColor: 'rgba(0, 0, 0, 0)'
				}));
			}

			this.content.addWithUpdate(new fabric.Text((this.id + 1) + '\n' + this.type, {
				minScaleLimit: 1,
				originX: 'center',
				originY: 'center',
				fontSize: 12,
				textAlign: 'center',
				fill: 'rgb(255, 255, 255)',
				backgroundColor: 'rgba(0, 0, 0, 0)'
			}));

			canvas.add(this.content);
			this.content.left = options.position[0];
			this.content.top = options.position[1];

			canvas.requestRenderAll();

			this.export = function () {
				return {
					type: this.type,
					shape: this.shape,
					color: this.color,
					position: [
						Math.floor(this.content.left),
						Math.floor(this.content.top)
					],
					size: [
						Math.floor(this.content._objects[0].width),
						Math.floor(this.content._objects[0].height)
					],
					angle: Math.floor(this.content.angle)
				};
			}

			return this;
		 }

	</script>

	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-117753493-1');
	</script>

	</body>
</html>
