/*
 | x²+(1-x²)cos(ð)			(1-cos(ð))xy-sin(ð)z	(1-cos(ð))xz + sin(ð)y	|
 | (1-cos(ð))yx-sin(ð)z		y²+(1-y²)cos(ð)			(1-cos(ð))yz + sin(ð)x	|
 | (1-cos(ð))zx-sin(ð)y		(1-cos(ð))zy-sin(ð)x	z²+(1-z²)cos(ð)			|
 
 
 che nel mio caso diventa (con z=0)
  
 | x²+(1-x²)cos(ð)	(1-cos(ð))xy	sin(ð)y	|
 | (1-cos(ð))yx		y²+(1-y²)cos(ð)	sin(ð)x	|
 | -sin(ð)y			sin(ð)x			cos(ð)	|
 
 
 * */
(
	function($){
		
		
		
		
		
		
		
		$.fn.tag_cloud = function(tags){
			
			var CLOUD = this;
			
			var num = 0;
			
			var grad = 2*Math.PI/360;
			
			var _debug = true;
			
			//debug function
			function debug(v){if(_debug)console.debug(v);}
			
			//per i gradi, restituisce in radianti n gradi
			function g(n){return grad*n;}
			//e inversa
			function _g(r){return r/grad;}
			
			
			
			
			/*
								  y
								||
						   o............O
							\	||      :
							 \	||      :
							  \	||      :         x
							 A \||======:=========
								//\     :
							  // T  \   :
							// 	      \ :
						  //````````````o
						z
			
			
			*/
			
			
			
			
			
			
			//oggetto principale
			var cloud = function(obj){
				debug( CLOUD );
				var event_handler = new eh();
				var engine = new engy();
				var projector = new proj();
				
				var points = [];
				
				//per ora è parecchio grezzo visto che li distribuisce a caso
				this.allocate_points = function(){
					debug( 'trying to allocate '+num+' points');
					for(var i = 0 ; i<num; i++){
						var tmp = new point3d(50, i*g(Math.random()*360),i*g(Math.random()*360));
						points.push(tmp);
						console.debug(projector.project(tmp));
					}
					debug( ''+num+' points allocated');
					debug( points);
					
				}
				
				
				this.init = function(){
					debug('initializing cloud');
					num = UTILITY__.count(tags);
					debug('counted '+num+' tags');
					this.allocate_points();
					
					
				};
				
				
				this.init();
				
			};
			
			//event handler
			var eh = function(obj){
				debug('new eh created');
			};
			
			//motore
			var engy = function(obj){
				debug('new engy created');
			};
			
			//point2d
			var point2d = function(x, y, zindex){
				this.x = x || 0;
				this.y = y || 0;
				this.zindex = zindex || 0;
				/* draw point */
				
				this.set = function(x, y, zindex){
					this.x = x;
					this.y = y;
					this.zindex = zindex
				};
				this.show = function(){
					return 'x: '+this.x+' ;  y: '+this.y+' ;  zindex: '+this.zindex;
				};
			};
			
			//point3d
			var point3d = function(r, theta, alpha){
				
				this.r = r || 0;
				this.theta = theta || 0;
				this.alpha = alpha || 0;
				
				this.set = function(r, t, a){
					this.r = r;
					this.theta = theta;
					this.alpha = alpha;
				};
				this.show = function(){
					return 'r: '+this.r+' ;  theta: '+this.theta+' ;  alpha: '+this.alpha;
				};
			};
			
			var vector = function(x, y, z){
				this.x = x;
				this.y = y;
				this.z = z;
				
				this.scal = function(a){
					return new vector(this.x*a,this.x*a,this.z*a );
				}
				
				this.p_scalar = function(vec){
					return this.x * vec.x + this.y * vec.y + this.z * vec.z ;
				}
				this.p_vector = function(vec){
					var mod = Math.sqrt(Math.pow(vec.x - this.x, 2) + Math.pow(vec.y - this.y, 2) + Math.pow(vec.z - this.z, 2));
					var tmp = new vector(
						this.y*vec.z - vec.y*this.z,
						-(this.x*vec.z - vec.x*this.z),
						this.x*vec.y - vec.y*this.x
					);
					var ret = tmp.scal(1/mod);
					return ret;
				}	
			};
			
			var matrix = function(x11,x12,x13, x21,x22,x23, x31,x32,x33){
				
			};
			
			var proj = function(){
				this.project = function(point3d){
					var x = point3d.r*Math.sin(point3d.theta)*Math.cos(point3d.alpha);
					var y = point3d.r*Math.sin(point3d.theta)*Math.sin(point3d.alpha);
					var z = point3d.r*Math.cos(point3d.theta);
					return new point2d(x, y, z);
				}
			};
			
			//UTILITY
			var UTILITY__ = {
				count : function(obj){
					var ret = 0;
					for(var i in obj){ret++;}
					return ret;
				},
				get_position : function(element){
					var off = jQuery(element).offset();
					return  new position( parseInt(off.left, 10), parseInt(off.top, 10) );
				},
					
				assert : function (condition, msg){
					if(!condition && opts.dbg){
						alert('ASSERT : \n'+msg);
					}
				},
				
				ext : function xxx(named, val){
					window[named] = val;
				},
				
				/* is possible to force debug */
				debug : function(msg, section, force){
					if(opts.dbg || force){
						var show = (opts.dbg_class === 'all')||(opts.dbg_class !== 'all' && (opts.dbg_class).search(section)!==-1);
						if(show){
							try {opera.postError(msg);}catch(e){}
							try {console.debug(msg);}catch(e){}
						}
					}
					
				},
				
				/*	Extract the number from id format used for images ( like xxsxssx_rerer_343) */
				myid : function(v){
					var p = v.split('_');
					 return parseInt(p[p.length-1],10);
				},
				
					
				get_relative_parent : function(ev, id){
			
					var target = jQuery('#'+id);
					
					var left = ev.pageX,
						top = ev.pageY;				
		
					/*click position relative to container*/
					var ret =  new position( left-conf.carouselX , top-conf.carouselY );
				
					/*click position relative to image*/
					var parent = target.parent();
					var parent_position = new position(
						parseInt(parent.css('left'), 10),
						parseInt(parent.css('top'), 10)
					);
		
					var click_point = new point(
						ret.left - parent_position.left,
						ret.top - parent_position.top
					);
					return click_point;
				},				
				
				
				drawLine : function(point1, point2, targetobj){
					var x1 = point1.x,
						y1 = point1.y,
						x2 = point2.x,
						y2 = point2.y;
	
					var dx = Math.abs(x1-x2),
						dy = Math.abs(y1-y2),
						const1=false,
						const2=false,
						p=false,
						x=false,
						y=false,
						step=false;
	
					if(dx >= dy){
						const1 = 2 * dy;
						const2 = 2 * (dy - dx);
						p = 2 * dy - dx;
						x = Math.min(x1,x2);
						y = (x1 < x2) ? y1 : y2;
						step = (y1 > y2) ? 1 : -1;
						UTILITY__.drawPoint(new point(x,y), targetobj);
		
						while (x < Math.max(x1,x2)){
							if (p < 0){
								p += const1;
							}else{
								y += step;
								p += const2;
							}
							UTILITY__.drawPoint(new point(x,y), targetobj);
						}
					}else{
						const1 = 2 * dx;
						const2 = 2 * (dx - dy);
						p = 2 * dx - dy;
						y = Math.min(y1,y2);
						x = (y1 < y2) ? x1 : x2;
						step = (x1 > x2) ? 1 : -1;
						UTILITY__.drawPoint(new point(x,y), targetobj);
						while (y <Math.max(y1,y2)){
							if (p < 0){
								p += const1;
							}else{
								x += step;
								p += const2;
							}
							UTILITY__.drawPoint(new point(x, ++y), targetobj);
						}
					}
					return this;
				},
				
				
				drawPoint : function(point, targetobj){
					var lenx = 1,
						leny = 1,
						adding_point = jQuery('<div>').css({
							position : 'absolute',
							top : point.y+'px',
							left : point.x+'px',
							width : lenx+'px',
							height : leny+'px',
							backgroundColor : 'white'
						});
					targetobj.append(adding_point);
				},
				
				
				mix_literal : function(arr){
					var count = 0,
						done = 0,
						insert = new Object(),
						ret = new Object();
						
					for(var el in arr){
						insert[count] = el;
						count++;
					}	
									
					for(;done<count;){
						var sel = true;
						while(sel){
							sel = insert[Math.floor(Math.random()*count)];
							if(typeof ret[sel] === 'undefined'){
								/*console.debug('writing '+ sel);*/
								ret[sel] = arr[sel];
								done++;
							}else{
								/*console.debug('NOT writing '+ sel);*/
							}
							sel = !sel;
						}
					}
					return ret;
				},
				
				get_vis_range : function(i, current, show_num, num){
					for(var i1= current, i2 = current, j = 0; j <= show_num; i1=(i1-1+num)%num, i2=(i2+1+num)%num, j++){
						if (i === i1 || i === i2){
							return true;
						}
					}
					return false;
				}
			};
			
		
		
		
			
			jQuery(
				function(){
					var C = new cloud();
					
				}
			);
			
		};
		  
	}
)(jQuery);
