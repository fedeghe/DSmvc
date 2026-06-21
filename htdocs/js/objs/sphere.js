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
	
	var tmp = [-size, 0, size],
		i = 0,
		x, y, z,
		theta, fi;
	for(x in tmp)
		for(y in tmp)
			for(z in tmp){
				i++;
				theta = g(Math.random() * 360);
				fi = g(Math.random() * 360);
				this.pointsArray.push(this.make3DPoint(
					parseInt(size*Math.cos(theta)*Math.sin(fi),10),
					parseInt(size*Math.cos(theta)*Math.cos(fi),10),
					parseInt(size*Math.sin(theta),10)
				));
			}
	
	

};

//Inherit DisplayObject3d methods and properties
Sphere.prototype = new ddd.DisplayObject3D();
