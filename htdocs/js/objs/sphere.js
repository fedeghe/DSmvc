var Sphere =  function (size){
	//if the size is not set then give a default
	if (size === undefined){
		size = 10;
	}
	var grad = 2*Math.PI/360;
	
	function g(n){return grad * n;}
	//e inversa
	function _g(r){return r / grad;}
    //make3dpoint is a function inherited from
    //DisplayObject3D
    
    
    this.pointsArray = new Array();
    

	
	//Create a 3d point for every point on the cube.
	
	var tmp = [-size, 0, size];
	var i = 0;
	for(var x in tmp)
		for(var y in tmp)
			for(var z in tmp){
				i++;
				//this.pointsArray.push(this.make3DPoint(tmp[x],tmp[y],tmp[z]));
				var theta = g(Math.random() * 360);
				var fi = g(Math.random() * 360);
				var x = parseInt(size*Math.cos(theta)*Math.sin(fi),10);
				var y = parseInt(size*Math.cos(theta)*Math.cos(fi),10);
				var z = parseInt(size*Math.sin(theta),10);
				this.pointsArray.push(this.make3DPoint(x,y,z));
			}
	
	

};

//Inherit DisplayObject3d methods and properties
Sphere.prototype = new DisplayObject3D();
