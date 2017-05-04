<?php 


/**
 * Class wrapping some basic structures that are used to represent a graph.
 */

class Graph {

	private $numNodes;
	private $numEdges;

	// special index assigment
	const NONE     = -1;
	const TERMINAL = -2;
	const ORPHAN   = -3;

	/////////////////////////
	// node representation //
	/////////////////////////

	// first outgoing edge
	private $firstOutgoings ;

	// parent (in the tree structure)
	private $parents ;

	// next active node
	private $nextNodes ;


	
	// distance to the terminal
	private $distances ;
	
	// timestamp indicating when distance was computed
	private $timestamps ;
	

	// indicates whether this node belongs to the sink or the source tree
	private $inSink ;

	// indicates whether this node was changed
	private $marked ;

	// indicates whether this node is in the changed list
	private $inChangedList ;

	// the residual capacity of this node to the sink (<0) or from the source
	// (>0)
	private  $residualNodeCapacities;

	/////////////////////////
	// edge representation //
	/////////////////////////

	// node the edge points to
	private $heads ;

	// next edge with the same originating node
	private $nextEdges ;

	// reverse arc
	private $sisters ;

	// residual capacity of this edge
	private $residualEdgeCapacities ;

	public function Graph($numNodes, $numEdges) {

		$this->numNodes = $numNodes;
		$this->numEdges = $numEdges;

		// allocate node data
		/*
		firstOutgoings         = new int[numNodes];
		parents                = new int[numNodes];
		nextNodes              = new int[numNodes];
		timestamps             = new int[numNodes];
		distances              = new int[numNodes];
		inSink                 = new boolean[numNodes];
		marked                 = new boolean[numNodes];
		inChangedList          = new boolean[numNodes];
		residualNodeCapacities = new float[2*numEdges];
		*/
		// allocate edge data
		/*
		heads                  = new int[2*numEdges];
		nextEdges              = new int[2*numEdges];
		sisters                = new int[2*numEdges];
		residualEdgeCapacities = new float[2*numEdges];
		*/
		// initialise node data
		echo "\n nombre de noeuds ".$this->numNodes;
		
		$this->firstOutgoings = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->firstOutgoings[]         = Graph::NONE;
			//echo "\n initialisation du noeud ".$i;
		}
		echo "\n fin initialisation des arc sortants";
		$this->parents = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->parents[]                = Graph::NONE;
		}
		echo "\n fin initialisation des noeuds sortants";
		$this->nextNodes = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->nextNodes[]              = Graph::NONE;
		}
		$this->timestamps = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->timestamps[]             = 0;
		}
		$this->distances = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->distances[]             = 0;
		}
		$this->inSink = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->inSink[]                 = false;
		}
		$this->marked = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->marked[]                 = false;
		}
		$this->residualNodeCapacities = array();
		for ($i = 0; $i < $this->numNodes; $i++) {
			$this->residualNodeCapacities[] = 0;
		}
			
			
			
			
		

		// initialise edge data
		$this->heads = array();
		for ($i = 0; $i < 2*$numEdges; $i++) {
			$this->heads[]                  = Graph::NONE;
		}
		$this->nextEdges = array();
		for ($i = 0; $i < 2*$numEdges; $i++) {
			$this->nextEdges[]              = Graph::NONE;
		}
		$this->sisters = array();
		for ($i = 0; $i < 2*$numEdges; $i++) {
			$this->sisters[]              = Graph::NONE;
		}
		$this->residualEdgeCapacities = array();
		for ($i = 0; $i < 2*$numEdges; $i++) {
			$this->residualEdgeCapacities[] = 0;
			//echo "\n noeud résiduel";
		}
	    echo "fin initialisation";
		
	}

	public final function getResidualNodeCapacity($node) {
		return $this->residualNodeCapacities[$node];
	}

	public final function setResidualNodeCapacity($node, $capacity) {
		$this->residualNodeCapacities[$node] = $capacity;
		//if($capacity < 0) echo "\n capacité inf à zero prise en compte";
	}

	public final function getResidualEdgeCapacity($edge) {
		return $this->residualEdgeCapacities[$edge];
	}

	public final function  setResidualEdgeCapacity($edge, $capacity) {
		$this->residualEdgeCapacities[$edge] = $capacity;
	}

	public final function getParent($node) {
		if($node<$this->numNodes){
			return $this->parents[$node];
		}else{
			return Graph::NONE;
		}
		
	}

	public final function setParent($node, $edge) {
		$this->parents[$node] = $edge;
	}

	public final function getSister($edge) {
		return $this->sisters[$edge];
	}

	public final function setSister($edge, $sister) {
		$this->sisters[$edge] = $sister;
	}

	public final function getNextNode($node) {
		return $this->nextNodes[$node];
	}

	public final function  setNextNode($node, $next) {
		$this->nextNodes[$node] = $next;
	}

	public final function getNextEdge($edge) {
		return $this->nextEdges[$edge];
	}

	public final function setNextEdge($edge, $next) {
		$this->nextEdges[$edge] = $next;
	}

	public final function getFirstOutgoing($node) {
		return $this->firstOutgoings[$node];
	}

	public final function setFirstOutgoing($node, $edge) {
		$this->firstOutgoings[$node] = $edge;
	}

	public final function getHead($edge) {
		return $this->heads[$edge];
	}

	public final function setHead($edge, $head) {
		$this->heads[$edge] = $head;
	}

	public final function getInSink($node) {
		if($node<$this->numNodes){
			return $this->inSink[$node];
		}else{
			return false;
		}
		
	}

	public final function  setInSink($node, $isIn) {
		$this->inSink[$node] = $isIn;
	}

	public final function getTimestamp($node) {
		return $this->timestamps[$node];
	}

	public final function setTimestamp($node, $time) {
		$this->timestamps[$node] = $time;
	}

	public final function getDistance($node) {
		return $this->distances[$node];
	}

	public final function setDistance($node, $distance) {
		$this->distances[$node] = $distance;
	}

	public final function  getInChangedList($node) {
		return $this->inChangedList[$node];
	}

	public final function setInChangedList($node, $isIn) {
		$this->inChangedList[$node] = $isIn;
	}

	public final function getNumNodes() {
		return $this->numNodes;
	}

	public final function getNumEdges() {
		return $this->numEdges;
	}

	public final function getMarked($node) {
		return $this->marked[$node];
	}

	public final function setMarked($node, $is) {
		$this->marked[$node] = $is;
	}
}

