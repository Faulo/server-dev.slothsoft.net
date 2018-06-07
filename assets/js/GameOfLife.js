
const ACTION = {
	DEBUG : -1,
	DEBUG_DUMP : -2,
	WORKER_INIT : 1,
	WORKER_INITIALIZED : 2,
	REQUEST_TICK : 11,
	REQUEST_DRAW : 21,
	REQUEST_DATA : 31,
};

function GameOfLife(parentNode) {
	this.parentNode = parentNode;
	this.config = {
		model : {
			fps : 100,
		},
		view : {
			fps : 30,
		},
		peepCount : 10,
		peepLimit : 100,
		viewport : {
			width : 1000,
			height: 500,
		},
	};
	
	this.initDocument();
}
GameOfLife.prototype.initDocument = function() {
	this.request = new XMLHttpRequest();
	this.request.open("GET", "/getResource.php/dev/GameOfLife", true);
	this.request.addEventListener(
		"load",
		this.receiveDocument.bind(this),
		false
	);
	
	this.request.send();
};
GameOfLife.prototype.receiveDocument = function(eve) {
	if (this.request.responseXML) {
		this.rootNode = this.parentNode.ownerDocument.importNode(this.request.responseXML.documentElement, true);
		this.parentNode.appendChild(this.rootNode);
		
		this.initWorker();
	}
};
GameOfLife.prototype.worker = undefined;
GameOfLife.prototype.initWorker = function() {
	this.worker = new Worker("/getScript.php/dev/GameOfLife.Worker");
	this.worker.addEventListener(
		"message",
		this.receiveMessage.bind(this),
		false
	);
	
	this.sendMessage(ACTION.WORKER_INIT, this.config);
};
GameOfLife.prototype.receiveMessage = function(eve) {
	this.parseMessage(eve.data.action, eve.data.payload);
};
GameOfLife.prototype.parseMessage = function(action, payload) {
	switch (action) {
		case ACTION.WORKER_INITIALIZED:
			this.initDraw();
			break;
		case ACTION.REQUEST_TICK:
			this.sendMessage(ACTION.REQUEST_TICK, payload);
			break;
		case ACTION.REQUEST_DRAW:
			this.draw(payload);
			break;
		case ACTION.DEBUG_DUMP:
			document.querySelector("pre").textContent = payload;
			break;
	}
	return false;
};
GameOfLife.prototype.sendMessage = function(action, payload) {
	this.worker.postMessage({
		action : action,
		payload : payload === undefined
			? null
			: payload
	});
};
GameOfLife.prototype.initDraw = function() {
	this.rootNode.setAttribute("viewBox", "0 0 " + this.config.viewport.width + " " + this.config.viewport.height);
	
	this.peepList = {};
	
	//this.requestData();
	window.setInterval(
		this.requestData.bind(this),
		1000 / this.config.view.fps
	);
};

GameOfLife.prototype.getPeepById = function(id) {
	return this.peepList[id];
};
GameOfLife.prototype.createPeep = function(data) {
	//console.log("creating peep...");
	var peep = new Peep(
		this.rootNode.querySelector("defs .peep").cloneNode(true),
		data,
		this.rootNode.createSVGTransform()
	);
	this.rootNode.appendChild(peep.getNode());
	this.peepList[peep.getId()] = peep;
	return peep;
};
GameOfLife.prototype.despawnPeep = function(peepId) {
	//console.log("despawning peep...");
	if (this.peepList[peepId]) {
		this.rootNode.removeChild(this.peepList[peepId].getNode());
		this.peepList[peepId] = null;
	}
};
GameOfLife.prototype.draw = function(data) {
	for (var i = 0; i < data.peepList.length; i++) {
		let peep;
		peep = this.getPeepById(data.peepList[i].id);
		if (!peep) {
			peep = this.createPeep(data.peepList[i]);
		}
		peep.updateData(data.peepList[i]);
	}
	for (var i = 0; i < data.despawnList.length; i++) {
		this.despawnPeep(data.despawnList[i]);
	}
	//document.body.textContent = data.join("\n");
	//this.drawNode.setAttribute("x", data.x + "%");
	//this.drawNode.setAttribute("y", data.y + "%");
	//this.drawNode.setAttribute("transform", "rotate(" + data.deg + ")");
	
	/*
	window.setTimeout(
		this.requestData.bind(this),
		10
	);
	//*/
		
};
GameOfLife.prototype.requestTick = function() {
	this.sendMessage(ACTION.REQUEST_TICK);
};
GameOfLife.prototype.requestData = function() {
	this.sendMessage(ACTION.REQUEST_DATA);
};


function Peep(node, data, drawNodeTransform) {
	this.node = node;
	this.data = data;
	
	this.node.setAttribute("data-peep-state", this.data.state);
	this.node.setAttribute("opacity", this.data.opacity / 100);
	
	this.drawNode = this.node.querySelector("rect");
	this.drawNode.setAttribute("width", this.data.size);
	this.drawNode.setAttribute("height", this.data.size);
	this.drawNode.setAttribute("fill", this.data.color);
	//this.drawNode.setAttribute("stroke", this.data.color);
	this.drawNodeTransform = drawNodeTransform;
	this.drawNodeTransform.setTranslate(this.data.x, this.data.y);
	this.node.transform.baseVal.appendItem(this.drawNodeTransform);
}
Peep.prototype.getId = function() {
	return this.data.id;
};
Peep.prototype.getNode = function() {
	return this.node;
};
Peep.prototype.updateData = function(data) {
	if (data.x !== this.data.x || data.y !== this.data.y) {
		this.data.x = data.x;
		this.data.y = data.y;
		this.drawNodeTransform.setTranslate(this.data.x, this.data.y);
	}
	if (data.size !== this.data.size) {
		this.data.size = data.size;
		this.drawNode.setAttribute("width", this.data.size);
		this.drawNode.setAttribute("height", this.data.size);
	}
	if (data.state !== this.data.state) {
		this.data.state = data.state;
		this.node.setAttribute("data-peep-state", this.data.state);
	}
	if (data.opacity !== this.data.opacity) {
		this.data.opacity = data.opacity;
		this.node.setAttribute("opacity", this.data.opacity / 100);
	}
};