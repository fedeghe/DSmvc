
		
		var DisplayObject3D = function(){
		    return this;
		};
		DisplayObject3D.prototype._x = 0;
		DisplayObject3D.prototype._y = 0;
		//Create 3d Points
		DisplayObject3D.prototype.make3DPoint = function(x,y,z) {
		    return {'x':x,'y':y, 'z':z};
		};

		//Create 2d Points
		DisplayObject3D.prototype.make2DPoint = function(x,y, depth, scaleFactor){
			return {'x':x,'y':y, 'depth':depth, 'scaleFactor':scaleFactor};
		};

		//Holds the container
		DisplayObject3D.prototype.container = undefined;

		//Holds an array of 3d points.
		DisplayObject3D.prototype.pointsArray = [];


		// Set the container and create place holders if
		// there is no <ul> in the container
		DisplayObject3D.prototype.init = function (container){

		    this.container = jQuery(container);
		    this.containerId = this.container.attr("id");

		    //if there isn't a ul than it creates a list of +'s
		    if (jQuery(container+":has(ul)").length === 0){
		        for (i=0; i < this.pointsArray.length; i++){
		            this.container.append('<b id="item'+i+'">+</b>');
		        }
		    }
		};
		
		
		
		
		//camera object
		var Camera3D = function (){};

		//The x,y,z of the camera
		Camera3D.prototype.x = 0;
		Camera3D.prototype.y = 0;
		Camera3D.prototype.z = 500;

		//Determines the zoom
		Camera3D.prototype.focalLength = 1000;

		//Figure out how large the object should be in
		//reference to the camera.
		Camera3D.prototype.scaleRatio = function(item){
		    return this.focalLength/(this.focalLength + item.z - this.z);
		};

		//Initialize the camera with values.
		Camera3D.prototype.init = function (x,y,z,focalLength){
		    this.x = x;
		    this.y = y;
		    this.z = z;
		    this.focalLength = focalLength;
		};
		
		
		
		
		
		
		
		// class creates an array of objects to
		// be rendered, and initializes them.
		var Object3D = function (container){
		    this.container = $(container);
		};

		Object3D.prototype.objects = [];

		//Add object to the list of objects.
		Object3D.prototype.addChild = function (object3D){

		    this.objects.push(object3D);

		    object3D.init(this.container);

		    return object3D;
		};
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		var Scene3D = function (){};

		Scene3D.prototype.sceneItems = [];

		//Adds objects to the list of items to be rendered.
		Scene3D.prototype.addToScene = function (object){
		    this.sceneItems.push(object);
		};


		//Converts a 3d point into a 2d point.
		Scene3D.prototype.Transform3DPointsTo2DPoints = function(points, axisRotations,camera){
		    var TransformedPointsArray = [];
		    var sx = Math.sin(axisRotations.x);
		    var cx = Math.cos(axisRotations.x);
		    var sy = Math.sin(axisRotations.y);
		    var cy = Math.cos(axisRotations.y);
		    var sz = Math.sin(axisRotations.z);
		    var cz = Math.cos(axisRotations.z);
		    var x,y,z, xy,xz, yx,yz, zx,zy, scaleFactor;

		    var i = points.length;

		    while (i--){
		        x = points[i].x;
		        y = points[i].y;
		        z = points[i].z;

		        // rotation around x
		        xy = cx*y - sx*z;
		        xz = sx*y + cx*z;
		        // rotation around y
		        yz = cy*xz - sy*x;
		        yx = sy*xz + cy*x;
		        // rotation around z
		        zx = cz*yx - sz*xy;
		        zy = sz*yx + cz*xy;

		        scaleFactor = camera.focalLength/(camera.focalLength + yz);
		        x = zx*scaleFactor;
		        y = zy*scaleFactor;
		        z = yz;

		        var displayObject = new DisplayObject3D();
		        TransformedPointsArray[i] = displayObject.make2DPoint(x, y, -z, scaleFactor);
		    }

		    return TransformedPointsArray;
		};


		//Takes the converted 2d and applies the appropriate CSS.
		Scene3D.prototype.renderCamera = function (camera){

		    // Loop through all objects in the scene.
		    for(var i = 0 ; i< this.sceneItems.length; i++){

		        var obj = this.sceneItems[i].objects[0];

		        //transform the points in the object to 2d points.
		        var screenPoints = this.Transform3DPointsTo2DPoints(obj.pointsArray, axisRotation, camera);


		        //does the container have a ul inside of it.
		        var hasList = (document.getElementById(obj.containerId).getElementsByTagName("ul").length > 0);


		        //Cycle through each point in the object.
		        for (k=0; k < obj.pointsArray.length; k++){
		            var currItem = null;

		            //if the container has a list then select the lis
		            if (hasList){
		                currItem = document.getElementById(obj.containerId).getElementsByTagName("ul")[0].getElementsByTagName("li")[k];
		            }else{

		                //otherwise select whatever is there.
		                currItem = document.getElementById(obj.containerId).getElementsByTagName("*")[k];
		            }

		            //If there are items to render then...
		            if(currItem){
		                currItem._x = screenPoints[k].x;
		                currItem._y = screenPoints[k].y;
		                currItem.scale = screenPoints[k].scaleFactor;


		                //Render the CSS.
		                currItem.style.position = "absolute";
		                currItem.style.top = currItem._y+'px';
		                currItem.style.left = currItem._x+'px';
		                currItem.style.fontSize = 100*currItem.scale+'%';

		                $(currItem).css({opacity:(currItem.scale-.5)});

		            }


		        }

		    }
		};

		//Center for rotation
		var axisRotation = new DisplayObject3D().make3DPoint(0,0,0);
		
		
		
		
		
