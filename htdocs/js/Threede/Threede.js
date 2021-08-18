/**
 * Threede namespace
 * @type {Object}
 */
var Threede = {};



Threede.tools = {
	
}


/**
 * Threede.Point2D
 * @param {[type]} x [description]
 * @param {[type]} y [description]
 */
Threede.Point2D = function (x, y) {
	this.x = x;
	this.y = y
};
Threede.Point2D.prototype = {
	distance : function (/* Point2D */ p) {
		return Math.sqrt ((this.x - p.x)*(this.x - p.x) + (this.y - p.y)*(this.y - p.y));
	}
};



/**
 * Threede.Segment
 * @param  {[type]} p1 [description]
 * @param  {[type]} p2 [description]
 * @return {[type]}    [description]
 */
Threede.Segment = function (p1, p2) {
	this.p1 = p1;
	this.p2 = p2;
};
Threede.Segment.prototype.length = function () {
	return this.p1.distance(this.p2);
};



/**
 * Threede.Triangle
 * @param {[type]} p1 [description]
 * @param {[type]} p2 [description]
 * @param {[type]} p3 [description]
 */
Threede.Triangle = function (p1, p2, p3) {
	this.p1 = p1;
	this.p2 = p2;
	this.p3 = p3;
};
Threede.Triangle.prototype = {
	get : {
		l12 : function () {this.l12 = this.p1.distance(this.p2);}, 
		l23 : function () {this.l23 = this.p2.distance(this.p3);}, 
		l31 : function () {this.l31 = this.p3.distance(this.p1);} 
	},
	area : function () {
		
	}
};