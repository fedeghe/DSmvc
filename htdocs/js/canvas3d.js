var canvas3d = (function() {

    var DisplayObject3D = function() {
    };
    DisplayObject3D.prototype._x = 0;
    DisplayObject3D.prototype._y = 0;
    DisplayObject3D.prototype.make3DPoint = function(x, y, z) {
        return {'x': x, 'y': y, 'z': z};
    };
    DisplayObject3D.prototype.make2DPoint = function(x, y, depth, scaleFactor) {
        return {'x': x, 'y': y, 'depth': depth, 'scaleFactor': scaleFactor};
    };
    DisplayObject3D.prototype.container = undefined;
    DisplayObject3D.prototype.pointsArray = [];
    DisplayObject3D.prototype.vertices = [];
    DisplayObject3D.prototype.faces = [];
    DisplayObject3D.prototype.init = function(container) {
        this.container = container;
        this.containerId = container.id || 'canvas3d';
    };

    var Camera3D = function() {};
    Camera3D.prototype.x = 0;
    Camera3D.prototype.y = 0;
    Camera3D.prototype.z = 500;
    Camera3D.prototype.focalLength = 1000;
    Camera3D.prototype.scaleRatio = function(item) {
        return this.focalLength / (this.focalLength + item.z - this.z);
    };
    Camera3D.prototype.init = function(x, y, z, focalLength) {
        this.x = x;
        this.y = y;
        this.z = z;
        this.focalLength = focalLength;
    };

    var Object3D = function(container) {
        this.container = container;
    };
    Object3D.prototype.objects = [];
    Object3D.prototype.addChild = function(object3D) {
        this.objects.push(object3D);
        object3D.init(this.container);
        return object3D;
    };

    var Scene3D = function(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.sceneItems = [];
    };
    Scene3D.prototype.addToScene = function(object) {
        this.sceneItems.push(object);
    };

    Scene3D.prototype.Transform3DPointsTo2DPoints = function(points, axisRotations, camera) {
        var TransformedPointsArray = [],
            m = rotationMatrix,
            x, y, z, xr, yr, zr, scaleFactor,
            i = points.length;
        while (i--) {
            x = points[i].x;
            y = points[i].y;
            z = points[i].z;
            xr = m[0]*x + m[1]*y + m[2]*z;
            yr = m[3]*x + m[4]*y + m[5]*z;
            zr = m[6]*x + m[7]*y + m[8]*z;
            scaleFactor = camera.focalLength / (camera.focalLength + zr);
            x = xr * scaleFactor;
            y = yr * scaleFactor;
            TransformedPointsArray[i] = this.make2DPoint(x, y, -zr, scaleFactor);
        }
        return TransformedPointsArray;
    };

    function cross2D(ax, ay, bx, by) {
        return ax * by - ay * bx;
    }

    function multiplyMatrices(a, b) {
        return [
            a[0]*b[0] + a[1]*b[3] + a[2]*b[6], a[0]*b[1] + a[1]*b[4] + a[2]*b[7], a[0]*b[2] + a[1]*b[5] + a[2]*b[8],
            a[3]*b[0] + a[4]*b[3] + a[5]*b[6], a[3]*b[1] + a[4]*b[4] + a[5]*b[7], a[3]*b[2] + a[4]*b[5] + a[5]*b[8],
            a[6]*b[0] + a[7]*b[3] + a[8]*b[6], a[6]*b[1] + a[7]*b[4] + a[8]*b[7], a[6]*b[2] + a[7]*b[5] + a[8]*b[8]
        ];
    }

    function applyRotationAxis(ax, ay, az, angle) {
        var len = Math.sqrt(ax*ax + ay*ay + az*az);
        if (len === 0) return;
        ax /= len; ay /= len; az /= len;
        var c = Math.cos(angle), s = Math.sin(angle), t = 1 - c;
        var rot = [
            t*ax*ax + c,     t*ax*ay - s*az,  t*ax*az + s*ay,
            t*ax*ay + s*az,  t*ay*ay + c,     t*ay*az - s*ax,
            t*ax*az - s*ay,  t*ay*az + s*ax,  t*az*az + c
        ];
        rotationMatrix = multiplyMatrices(rot, rotationMatrix);
    }

    var rotationMatrix = [1,0,0, 0,1,0, 0,0,1];

    Scene3D.prototype.make2DPoint = function(x, y, depth, scaleFactor) {
        return {'x': x, 'y': y, 'depth': depth, 'scaleFactor': scaleFactor};
    };

    Scene3D.prototype.renderCamera = function(camera) {
        var ctx = this.ctx,
            width = this.canvas.width,
            height = this.canvas.height;
        ctx.clearRect(0, 0, width, height);

        for (var i = 0; i < this.sceneItems.length; i++) {
            var obj = this.sceneItems[i].objects[0];
            if (!obj) continue;

            var source = (obj.vertices && obj.vertices.length) ? obj.vertices : obj.pointsArray;
            var screenPts = this.Transform3DPointsTo2DPoints(source, axisRotation, camera);

            if (obj.faces && obj.faces.length) {
                var drawList = [];

                for (var f = 0; f < obj.faces.length; f++) {
                    var face = obj.faces[f];
                    var idx = face.indices;
                    if (!idx || idx.length < 3) continue;

                    var p0 = screenPts[idx[0]];
                    var p1 = screenPts[idx[1]];
                    var p2 = screenPts[idx[2]];

                    // back-face culling via cross product in screen space
                    if (face.cull !== false) {
                        var cross = cross2D(p1.x - p0.x, p1.y - p0.y, p2.x - p0.x, p2.y - p0.y);
                        if (cross < 0) continue;
                    }

                    var avgDepth = 0;
                    for (var vi = 0; vi < idx.length; vi++) {
                        avgDepth += screenPts[idx[vi]].depth;
                    }
                    avgDepth /= idx.length;

                    var projVerts = [];
                    for (var vi = 0; vi < idx.length; vi++) {
                        var pt = screenPts[idx[vi]];
                        projVerts.push({
                            x: pt.x + width / 2,
                            y: pt.y + height / 2,
                            scale: pt.scaleFactor
                        });
                    }

                    drawList.push({
                        type: 'face',
                        depth: avgDepth,
                        verts: projVerts,
                        color: face.color || '#00FF00',
                        wireframe: face.wireframe !== false
                    });
                }

                if (obj.showAxes) {
                    var axisLen = obj.axisLength || 200;
                    var segCount = obj.axisSegments || 40;
                    var axesDirs = [[1,0,0], [0,1,0], [0,0,1]];
                    var allAxisPts = [];
                    for (var a = 0; a < axesDirs.length; a++) {
                        var dir = axesDirs[a];
                        for (var s = 0; s <= segCount; s++) {
                            var t = -axisLen + (2 * axisLen * s / segCount);
                            allAxisPts.push(obj.make3DPoint(dir[0]*t, dir[1]*t, dir[2]*t));
                        }
                    }
                    var axisScreenPts = this.Transform3DPointsTo2DPoints(allAxisPts, axisRotation, camera);
                    var idxOff = 0;
                    for (var a = 0; a < axesDirs.length; a++) {
                        for (var s = 0; s < segCount; s++) {
                            var ap1 = axisScreenPts[idxOff + s];
                            var ap2 = axisScreenPts[idxOff + s + 1];
                            var avgD = (ap1.depth + ap2.depth) / 2;
                            drawList.push({
                                type: 'axis',
                                depth: avgD,
                                x1: ap1.x + width / 2,
                                y1: ap1.y + height / 2,
                                x2: ap2.x + width / 2,
                                y2: ap2.y + height / 2
                            });
                        }
                        idxOff += segCount + 1;
                    }
                }

                drawList.sort(function(a, b) {
                    return a.depth - b.depth;
                });

                for (var d = 0; d < drawList.length; d++) {
                    var item = drawList[d];
                    if (item.type === 'face') {
                        var verts = item.verts;

                        ctx.beginPath();
                        ctx.moveTo(verts[0].x, verts[0].y);
                        for (var v = 1; v < verts.length; v++) {
                            ctx.lineTo(verts[v].x, verts[v].y);
                        }
                        ctx.closePath();

                        ctx.fillStyle = item.color;
                        ctx.globalAlpha = 0.85;
                        ctx.fill();
                        ctx.globalAlpha = 1;

                        if (item.wireframe) {
                            ctx.strokeStyle = '#AAAAAA';
                            ctx.lineWidth = 1.2;
                            ctx.stroke();
                        }
                    } else if (item.type === 'axis') {
                        ctx.beginPath();
                        ctx.moveTo(item.x1, item.y1);
                        ctx.lineTo(item.x2, item.y2);
                        ctx.strokeStyle = '#FFFFFF';
                        ctx.lineWidth = 1.5;
                        ctx.setLineDash([4, 4]);
                        ctx.stroke();
                        ctx.setLineDash([]);
                    }
                }
            } else if (obj.pointsArray && obj.pointsArray.length) {
                // fallback particle rendering
                var colors = ['#00FF00','#FF0000','#0000FF','#00FFFF','#FFFF00','#FF00FF'];
                var sorted = [];
                for (var k = 0; k < obj.pointsArray.length; k++) {
                    sorted.push({idx: k, pt: screenPts[k]});
                }
                sorted.sort(function(a, b) {
                    return a.pt.depth - b.pt.depth;
                });

                for (var k = 0; k < sorted.length; k++) {
                    var entry = sorted[k],
                        pt = entry.pt,
                        idx = entry.idx,
                        x = pt.x + width / 2,
                        y = pt.y + height / 2,
                        scale = pt.scaleFactor,
                        radius = Math.max(2, 5 * scale),
                        color = colors[idx % colors.length];

                    ctx.beginPath();
                    ctx.arc(x, y, radius, 0, Math.PI * 2);
                    ctx.fillStyle = color;
                    ctx.shadowColor = color;
                    ctx.shadowBlur = 10;
                    ctx.globalAlpha = Math.max(0, Math.min(1, scale - 0.5));
                    ctx.fill();
                    ctx.shadowBlur = 0;
                    ctx.globalAlpha = 1;
                }
            }
        }
    };

    Scene3D.prototype.enableInteraction = function(camera) {
        var canvas = this.canvas;
        var isDragging = false;
        var lastX = 0, lastY = 0;
        var sens = 0.005;
        var zoomSens = 2;

        canvas.addEventListener('mousedown', function(e) {
            isDragging = true;
            lastX = e.clientX;
            lastY = e.clientY;
            e.preventDefault();
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            var dx = e.clientX - lastX;
            var dy = e.clientY - lastY;
            lastX = e.clientX;
            lastY = e.clientY;

            var angleX = dy * sens;
            var angleY = -dx * sens;
            var cx = Math.cos(angleX), sx = Math.sin(angleX);
            var cy = Math.cos(angleY), sy = Math.sin(angleY);
            var rotX = [1,0,0, 0,cx,-sx, 0,sx,cx];
            var rotY = [cy,0,sy, 0,1,0, -sy,0,cy];
            var rot = multiplyMatrices(rotY, rotX);
            rotationMatrix = multiplyMatrices(rot, rotationMatrix);

            e.preventDefault();
        });

        canvas.addEventListener('mouseup', function() {
            isDragging = false;
        });
        canvas.addEventListener('mouseleave', function() {
            isDragging = false;
        });

        canvas.addEventListener('wheel', function(e) {
            e.preventDefault();
            camera.z += e.deltaY * zoomSens;
            if (camera.z < 10) camera.z = 10;
            if (camera.z > 2000) camera.z = 2000;
        }, {passive: false});

        // Touch support
        canvas.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                isDragging = true;
                lastX = e.touches[0].clientX;
                lastY = e.touches[0].clientY;
            }
            e.preventDefault();
        }, {passive: false});

        canvas.addEventListener('touchmove', function(e) {
            if (!isDragging || e.touches.length !== 1) return;
            var dx = e.touches[0].clientX - lastX;
            var dy = e.touches[0].clientY - lastY;
            lastX = e.touches[0].clientX;
            lastY = e.touches[0].clientY;

            var angleX = dy * sens;
            var angleY = dx * sens;
            var cx = Math.cos(angleX), sx = Math.sin(angleX);
            var cy = Math.cos(angleY), sy = Math.sin(angleY);
            var rotX = [1,0,0, 0,cx,-sx, 0,sx,cx];
            var rotY = [cy,0,sy, 0,1,0, -sy,0,cy];
            var rot = multiplyMatrices(rotY, rotX);
            rotationMatrix = multiplyMatrices(rot, rotationMatrix);

            e.preventDefault();
        }, {passive: false});

        canvas.addEventListener('touchend', function() {
            isDragging = false;
        });
    };

    /*
     | canvas3d Model Format (subset)
     |
     | JSON model:
     | {
     |   "vertices": [[x, y, z], [x, y, z], ...],
     |   "faces": [
     |     {"indices": [i0, i1, i2], "color": "#RRGGBB", "wireframe": true, "cull": true},
     |     ...
     |   ]
     | }
     |
     | vertices: array of [x,y,z] coordinates
     | faces: array of polygons (triangles preferred; quads accepted, split internally)
     |   - indices: vertex indices defining the polygon in CCW order
     |   - color: hex fill color (default #00FF00)
     |   - wireframe: draw edges (default true)
     |   - cull: back-face culling enabled (default true)
     */

    var ModelLoader = function() {};
    ModelLoader.prototype.load = function(json) {
        var model = new DisplayObject3D();
        model.vertices = [];
        model.faces = [];

        if (json.vertices) {
            for (var i = 0; i < json.vertices.length; i++) {
                var v = json.vertices[i];
                model.vertices.push(model.make3DPoint(v[0], v[1], v[2]));
            }
        }

        if (json.faces) {
            for (var i = 0; i < json.faces.length; i++) {
                var f = json.faces[i];
                var indices = f.indices;
                var color = f.color || '#00FF00';
                var wireframe = f.wireframe !== false;
                var cull = f.cull !== false;

                if (indices.length === 3) {
                    model.faces.push({
                        indices: [indices[0], indices[1], indices[2]],
                        color: color,
                        wireframe: wireframe,
                        cull: cull
                    });
                } else if (indices.length === 4) {
                    // Quad -> 2 triangles (fan from first vertex)
                    model.faces.push({
                        indices: [indices[0], indices[1], indices[2]],
                        color: color,
                        wireframe: wireframe,
                        cull: cull
                    });
                    model.faces.push({
                        indices: [indices[0], indices[2], indices[3]],
                        color: color,
                        wireframe: wireframe,
                        cull: cull
                    });
                } else if (indices.length > 4) {
                    // Fan triangulation for n-gons
                    for (var t = 1; t < indices.length - 1; t++) {
                        model.faces.push({
                            indices: [indices[0], indices[t], indices[t + 1]],
                            color: color,
                            wireframe: wireframe,
                            cull: cull
                        });
                    }
                }
            }
        }

        return model;
    };

    var axisRotation = new DisplayObject3D().make3DPoint(0, 0, 0);

    function applyRotation(axis, angle) {
        var c = Math.cos(angle), s = Math.sin(angle);
        var rot;
        if (axis === 'x') rot = [1,0,0, 0,c,-s, 0,s,c];
        else if (axis === 'y') rot = [c,0,s, 0,1,0, -s,0,c];
        else if (axis === 'z') rot = [c,-s,0, s,c,0, 0,0,1];
        else return;
        rotationMatrix = multiplyMatrices(rot, rotationMatrix);
    }

    return {
        DisplayObject3D: DisplayObject3D,
        Camera3D: Camera3D,
        Object3D: Object3D,
        Scene3D: Scene3D,
        ModelLoader: ModelLoader,
        axisRotation: axisRotation,
        applyRotation: applyRotation,
        applyRotationAxis: applyRotationAxis
    };
})();
