var Cube =  function (size){
   
    //if the size is not set then give a default
    if (size === undefined){
        size = 10;
    }
   
    //Create a 3d point for every point on the cube.
    this.pointsArray = [
   
    //make3dpoint is a function inherited from
    //DisplayObject3D
   
        this.make3DPoint(0,0,0),
		this.make3DPoint(-size,-size,-size),
        this.make3DPoint(size,-size,-size),
        this.make3DPoint(size,-size,size),
        this.make3DPoint(-size,-size,size),
        this.make3DPoint(-size,size,-size),
        this.make3DPoint(size,size,-size),
        this.make3DPoint(size,size,size),
        this.make3DPoint(-size,size,size),
        this.make3DPoint(0,size,-size),
        this.make3DPoint(size,size,0),
        this.make3DPoint(0,size,size),
        this.make3DPoint(-size,size,0),
        this.make3DPoint(0,-size,-size),
        this.make3DPoint(size,-size,0),
        this.make3DPoint(0,-size,size),
        this.make3DPoint(-size,-size,0),
        this.make3DPoint(-size,0,-size),
        this.make3DPoint(size,0,-size),
        this.make3DPoint(size,0,size),
        this.make3DPoint(-size,0,size),
		
        this.make3DPoint(0,size,0),
        this.make3DPoint(0,0,size),
        this.make3DPoint(size,0,0),
        this.make3DPoint(-size,0,0),
		
        this.make3DPoint(0,0,-size),
        this.make3DPoint(0,-size,0),

    ];
   
};

//Inherit DisplayObject3d methods and properties
Cube.prototype = new DisplayObject3D();