"use strict";

const ACTION = {
	DEBUG : -1,
	DEBUG_DUMP : -2,
	WORKER_INIT : 1,
	WORKER_INITIALIZED : 2,
	REQUEST_TICK : 11,
	REQUEST_DRAW : 21,
	REQUEST_DATA : 31,
};


self.sendMessage = function(action, payload) {
	postMessage({
		action : action,
		payload : payload === undefined
			? null
			: payload
	});
};
self.receiveMessage = function(eve) {
	parseMessage(eve.data.action, eve.data.payload);
};
self.parseMessage = function(action, payload) {
	switch (action) {
		case ACTION.WORKER_INIT:
			init(payload);
			break;
		case ACTION.REQUEST_TICK:
			tick();
			break;
		case ACTION.REQUEST_DRAW:
			sendMessage(ACTION.REQUEST_DRAW, payload);
			break;
		case ACTION.REQUEST_DATA:
			sendData();
			break;
		case ACTION.DEBUG_DUMP:
			sendMessage(ACTION.DEBUG_DUMP, payload);
			break;
	}
	return false;
};
self.addEventListener(
	"message",
	self.receiveMessage,
	false
);

self.model = undefined;
self.init = function(config) {
	model = new Model(config);
	sendMessage(ACTION.WORKER_INITIALIZED);
	self.setInterval(
		self.tick,
		1000 / config.model.fps
	);
};
self.tick = function() {
	model.tick();
	//sendMessage(ACTION.REQUEST_DRAW, model.getData());
};
self.sendData = function() {
	sendMessage(ACTION.REQUEST_DRAW, model.getData());
};


function Model(config) {
	this.config = config;
	this.map = new BoundingRect(0, 0, this.config.viewport.width, this.config.viewport.height);
	
	this.peepCount = 0;
	this.peepList = [];
	this.despawnList = [];
	for (var i = 0; i < this.config.peepCount; i++) {
		this.spawnPeep(
			(this.map.width-50) * Math.random(),
			(this.map.height-50) * Math.random(),
			50 * Math.random(),
			360 * Math.random(),
			Helper.getRandomColor(),
			true
		);
	}
}
Model.prototype.tick = function() {
	for (var i = 0; i < this.peepList.length; i++) {
		if (this.peepList[i]) {
			this.peepList[i].tick();
		}
	}
	my_dump({ peepCount : this.peepCount });
};
Model.prototype.spawnPeep = function(x, y, size, deg, color, checkCollision) {
	if (this.peepCount < this.config.peepLimit) {
		var peep, collides;
		peep = new Peep(
			this,
			this.peepList.length,
			x,
			y,
			size,
			deg,
			color
		);
		collides = false;
		if (checkCollision) {
			for (var j = 0; j < this.peepList.length; j++) {
				if (Helper.peepsCollide(peep, this.peepList[j])) {
					collides = true;
					break;
				}
			}
		}
		if (!collides) {
			this.peepList.push(peep);
			this.peepCount++;
		}
	}
};
Model.prototype.despawnPeep = function(peepId) {
	if (this.peepList[peepId]) {
		this.peepList[peepId] = null;
		this.despawnList.push(peepId);
		this.peepCount--;
	}
};
Model.prototype.getData = function() {
	var data = {};
	data.peepList = [];
	for (var i = 0; i < this.peepList.length; i++) {
		if (this.peepList[i]) {
			data.peepList.push(this.peepList[i].getData());
		}
	}
	data.despawnList = this.despawnList;
	this.despawnList = [];
	return data;
};

function Peep(model, id, x, y, size, deg, color) {
	this.ownerModel = model;
	this.data = {
		id : id,
		x : x,
		y : y,
		deg : deg,
		velocity : 0.5,
		size : size,
		opacity : 100,
		color : color, //["red", "green", "blue", "yellow"][parseInt(Math.random() * 4)],
		state : "child",
	};
}
Peep.prototype.tick = function() {
	switch (this.data.state) {
		case "child":
		case "adult":
		case "old":
			this.data.deg += (0.5 - Math.random()) / 2;
			this.data.velocity += (0.5 - Math.random()) / 10;
			this.growBy(
				Math.random() / 100
			);
			
			this.moveBy(
				Math.cos(this.data.deg) * this.data.velocity * Math.sqrt(50) / Math.sqrt(this.data.size),
				Math.sin(this.data.deg) * this.data.velocity * Math.sqrt(50) / Math.sqrt(this.data.size)
			);
			
			if (this.data.size > 20) {
				this.data.state = "adult";
			}
			if (this.data.size > 40) {
				this.data.state = "old";
			}
			if (this.data.size > 60) {
				this.data.state = "dying";
			}
			break;
		case "dying":
			this.data.opacity -= Math.random() / 4;
			if (this.data.opacity < 0) {
				this.data.opacity = 0;
				this.data.state = "dead";
			}
			break;
		case "dead":
			this.ownerModel.despawnPeep(this.getId());
			break;
	}
	
	
	//*/
	
	/*
	this.data.x += 0.0; //Math.cos(this.data.deg) * this.data.velocity;
	
	this.data.y += 0.0; //Math.sin(this.data.deg) * this.data.velocity;
	
	/*
	if (this.data.x > this.ownerModel.config.viewport.width) {
		this.data.x = 0.0;
	}
	if (this.data.x < 0.0) {
		this.data.x = this.ownerModel.config.viewport.width;
	}
	if (this.data.y > this.ownerModel.config.viewport.height) {
		this.data.y = 0.0;
	}
	if (this.data.y < 0.0) {
		this.data.y = this.ownerModel.config.viewport.height;
	}
	//*/
};
Peep.prototype.getId = function() {
	return this.data.id;
};
Peep.prototype.getColor = function() {
	return this.data.color;
};
Peep.prototype.getDeg = function() {
	return this.data.deg;
};
Peep.prototype.isAdult = function() {
	return this.data.state === "adult";
};
Peep.prototype.growBy = function(size) {
	this.data.size += size;
	this.data.x -= size / 2;
	this.data.y -= size / 2;
};
Peep.prototype.moveBy = function(stepX, stepY) {
	this.data.x += stepX;
	this.data.y += stepY;
	
	var list = this.getBoundingPoints();
	//my_dump(list);
	for (var i = 0; i < list.length; i++) {
		if (Helper.isOutside(this.ownerModel.map, list[i])) {
			//my_dump({rect : this.ownerModel.map, point : list[i]});
			this.data.x -= stepX;
			this.data.y -= stepY;
			this.data.deg += 180;
			return;
		}
	}
	for (var i = 0; i < this.ownerModel.peepList.length; i++) {
		if (this.ownerModel.peepList[i]) {
			if (this.ownerModel.peepList[i] !== this) {
				if (Helper.peepsCollide(this, this.ownerModel.peepList[i])) {
					
					if (this.isAdult() && this.ownerModel.peepList[i].isAdult()) {
						/*
						my_dump({
							x : [this.getCenterPoint().x, this.ownerModel.peepList[i].getCenterPoint().x, (this.getCenterPoint().x + this.ownerModel.peepList[i].getCenterPoint().x) / 2],
							y : [this.getCenterPoint().y, this.ownerModel.peepList[i].getCenterPoint().y, (this.getCenterPoint().y + this.ownerModel.peepList[i].getCenterPoint().y) / 2],
							color : [this.getColor().toString(), this.ownerModel.peepList[i].getColor().toString(), Helper.getNewColor(this.getColor(), this.ownerModel.peepList[i].getColor()).toString()],
						});
						//*/
						this.ownerModel.spawnPeep(
							(this.getCenterPoint().x + this.ownerModel.peepList[i].getCenterPoint().x) / 2,
							(this.getCenterPoint().y + this.ownerModel.peepList[i].getCenterPoint().y) / 2,
							0,
							(this.getDeg() + this.ownerModel.peepList[i].getDeg()) / 2,
							Helper.getNewColor(this.getColor(), this.ownerModel.peepList[i].getColor()),
							false
						);
					}
					
					this.data.x -= stepX;
					this.data.y -= stepY;
					this.data.deg += 180;
					
					return;
				}
			}
		}
	}
};
Peep.prototype.getData = function() {
	return {
		id : this.data.id,
		x : parseInt(this.data.x),
		y : parseInt(this.data.y),
		size : parseInt(this.data.size),
		opacity : parseInt(this.data.opacity),
		color : this.data.color.toString(),
		state : this.data.state,
	};
};
Peep.prototype.getBoundingRect = function() {
	return new BoundingRect(this.data.x, this.data.y, this.data.size, this.data.size);
};
Peep.prototype.getBoundingPoints = function() {
	return this.getBoundingRect().getBoundingPoints();
};
Peep.prototype.getCenterPoint = function() {
	return new BoundingPoint(this.data.x + this.data.size / 2, this.data.y + this.data.size / 2);
};

function BoundingRect(x, y, width, height) {
	this.x = x;
	this.y = y;
	this.width = width;
	this.height = height;
}
BoundingRect.prototype.getBoundingPoints = function() {
	return [
		new BoundingPoint(this.x, this.y),
		new BoundingPoint(this.x + this.width, this.y),
		new BoundingPoint(this.x, this.y + this.height),
		new BoundingPoint(this.x + this.width, this.y + this.height),
	];
};
function BoundingPoint(x, y) {
	this.x = x;
	this.y = y;
}

function Color(r, g, b) {
	this.r = r;
	this.g = g;
	this.b = b;
}
Color.prototype.toString = function() {
	return "rgb(" + [this.r, this.g, this.b].join(",") + ")";
};

self.Helper = {
	peepsCollide : function(peepA, peepB) {
		var list, box;
		list = peepA.getBoundingPoints();
		box = peepB.getBoundingRect();
		for (var i = 0; i < list.length; i++) {
			if (this.isInside(box, list[i])) {
				return true;
			}
		}
		list = peepB.getBoundingPoints();
		box = peepA.getBoundingRect();
		for (var i = 0; i < list.length; i++) {
			if (this.isInside(box, list[i])) {
				return true;
			}
		}
		return false;
	},
	isInside : function(rect, point) {
		return (point.x > rect.x && point.x < (rect.x + rect.width) && point.y > rect.y && point.y < (rect.y + rect.height));
	},
	isOutside : function(rect, point) {
		return !this.isInside(rect, point);
	},
	getRandomInteger : function(min, max) {
		return (max < min)
			? this.getRandomInteger(max, min)
			: parseInt(Math.random() * (max - min) + min);
	},
	getRandomColor : function() {
		return new Color(
			this.getRandomInteger(0, 255),
			this.getRandomInteger(0, 255),
			this.getRandomInteger(0, 255)
		);
	},
	getNewColor : function(colorA, colorB) {
		return new Color(
			this.getRandomInteger(colorA.r, colorB.r),
			this.getRandomInteger(colorA.g, colorB.g),
			this.getRandomInteger(colorA.b, colorB.b)
		);
	},
};

function my_dump(data) {
	sendMessage(ACTION.DEBUG_DUMP, JSON.stringify(data));
}
