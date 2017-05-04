<?php 

/**
 * Graph cut implementation for images.
 *
 * This implementation was <b>heavily</b> inspired by the implementation
 * provided by Kolmogorov and Boykov: MAXFLOW version 3.01.
 *
 * From the README of the library:
 *
 *	This software library implements the maxflow algorithm described in
 *
 *	"An Experimental Comparison of Min-Cut/Max-Flow Algorithms for Energy
 *	Minimization in Vision."
 *	Yuri Boykov and Vladimir Kolmogorov.
 *	In IEEE Transactions on Pattern Analysis and Machine Intelligence
 *	(PAMI),
 *	September 2004
 *
 *	This algorithm was developed by Yuri Boykov and Vladimir Kolmogorov
 *	at Siemens Corporate Research. To make it available for public
 *	use, it was later reimplemented by Vladimir Kolmogorov based on open
 *	publications.
 *
 *	If you use this software for research purposes, you should cite
 *	the aforementioned paper in any resulting publication.
 *
 * @author Jan Funke <jan.funke@inf.tu-dresden.de>
 * @version 0.1
 */
include_once "../entities/Graph.class.php";
include_once "../entities/Terminal.class.php";

/*import java.util.LinkedList;
import java.util.List;*/

/**
 * Class implementing the grach cut algorithm.
 */
class GraphCut {

	// graph structure
	public $graph;

	// counter for initialisation of edges
	private $edgeNum;

	// the total flow in the whole graph
	private $totalFlow;

	// counter for the numbers of iterations to maxflow
	private $maxflowIteration;

	// Lists of active nodes: activeQueueFirst points to first
	// elements of the lists, $this->activeQueueLast to the last ones.
	// In between, nodes are connected via reference to next node
	// in each node.
	private  $activeQueueFirst = array();
	private $activeQueueLast = array();

	// list of orphans
	private $orphans; 

	// counter for iterations of main loop
	private $time;
	
	
	//for me
	private $img_name;
	private $ext;

	/**
	 * Initialises the graph cut implementation and allocates the memory needed
	 * for the given number of nodes and edges.
	 *
	 * @param numNodes The number of nodes that should be created.
	 * @param numEdges The number of edges that you can add. A directed edge and its
	 *                 counterpart (i.e., the directed edge in the other
	 *                 direction) count as one edge.
	 */
	function  __construct($numNodes, $numEdges,$img,$ext) {
		$this->graph            = new Graph($numNodes, $numEdges);
		$this->edgeNum          = 0;
		$this->totalFlow        = 0;
		$this->maxflowIteration = 0;
		$this->activeQueueFirst = array(null,null);
		$this->activeQueueLast  = array(null,null);
		$this->orphans          = new SplDoublyLinkedList();
		$this->img_name = $img;
		$this->ext = $ext;
	}

	/**
	 * Set the affinity for one node to belong to the foreground (i.e., source)
	 * or background (i.e., sink).
	 *
	 * @param node   The number of the node.
	 * @param source The affinity of this node to the foreground (i.e., source)
	 * @param sink   The affinity of this node to the background (i.e., sink)
	 */
	public function setTerminalWeights($node, $source, $sink) {

		$delta = $this->graph->getResidualNodeCapacity($node);
		//echo '\n delta : \n';
		//var_dump($delta);
		if ($delta > 0)
			$source += $delta;
		else
			$sink   -= $delta;

		$this->totalFlow += ($source < $sink) ? $source : $sink;
		
		$this->graph->setResidualNodeCapacity($node, $source -$sink);
		//if( $this->graph->getResidualNodeCapacity($node) < 0); echo "nouvelle valeur inférieure à 0";
	}

	/**
	 * Set the edge weight of an undirected edge between two nodes.
	 *
	 * Please note that you cannot call any <tt>setEdgeWeight</tt> more often
	 * than the number of edges you specified at the time of construction!
	 *
	 * @param node1   The first node.
	 * @param node2   The second node.
	 * @param weight  The weight (i.e., the cost) of the connecting edge.
	 */
	public function setEdgeWeight($node1, $node2, $weight) {

		$this->setEdgeWeightDouble($node1, $node2, $weight, $weight);
	}

	/**
	 * Set the edge weight of a pair of directed edges between two nodes.
	 *
	 * Please note that you cannot call any <tt>setEdgeWeight</tt> more often
	 * than the number of edges you specified at the time of construction!
	 *
	 * @param node1      The first node.
	 * @param node2      The second node.
	 * @param weight1to2 The weight (i.e., the cost) of the directed edge from
	 *                   node1 to node2.
	 * @param weight2to1 The weight (i.e., the cost) of the directed edge from
	 *                   node2 to node1.
	 */
	public function  setEdgeWeightDouble($node1, $node2, $weight1to2, $weight2to1) {

		// get edge indices
		$edge        = $this->edgeNum; $this->edgeNum++;
		$reverseEdge = $this->edgeNum; $this->edgeNum++;

		// link edges
		$this->graph->setSister($edge, $reverseEdge);
		$this->graph->setSister($reverseEdge, $edge);

		// add node1 to edge
		$this->graph->setNextEdge($edge, $this->graph->getFirstOutgoing($node1));
		$this->graph->setFirstOutgoing($node1, $edge);

		// add node2 to reverseEdge
		$this->graph->setNextEdge($reverseEdge, $this->graph->getFirstOutgoing($node2));
		$this->graph->setFirstOutgoing($node2, $reverseEdge);

		// set targets of edges
		$this->graph->setHead($edge, $node2);
		$this->graph->setHead($reverseEdge, $node1);

		// set residual capacities
		$this->graph->setResidualEdgeCapacity($edge, $weight1to2);
		$this->graph->setResidualEdgeCapacity($reverseEdge, $weight2to1);
	}

	public function testImage($iter,$img,$ext){
		echo "\n **********enregistrement d'une image";
		$w = 225;
		$h = 225;
		$graphcutImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				//if($this->getTerminal($y*$w + $x) == Terminal::BACKGROUND){
			    if($this->getTerminal($y*$w + $x) == Terminal::FOREGROUND){
				//if($this->graph->getResidualNodeCapacity($y*$w+$x) != 0) echo "\n cap résiduelle non nulle";
			    //if($this->graph->getResidualNodeCapacity($y*$w+$x) > 0){
					$color = imagecolorallocate($graphcutImage,255,255,255);
					imagesetpixel($graphcutImage, $x, $y, $color);
	
				}else{
					//echo "other";
					$color = imagecolorallocate($graphcutImage,0,0,0);
					imagesetpixel($graphcutImage, $x, $y, $color);
				}
			}
		}
	
		Database_Manager::produce_image($graphcutImage,Constants::getGraphCutImageDir(),$img."_graphcut_$iter",$ext);
	}
	/**
	 * Performs the actual max-flow/min-cut computation.
	 *
	 * @param reuseTrees   reuse trees of a previos call
	 * @param changedNodes list of nodes that potentially changed their
	 *                     segmentation compared to a previous call, can be set
	 *                     to <tt>null</tt>
	 */
	public function computeMaximumFlow($reuseTrees, /*list*/ $changedNodes) {

		if ($this->maxflowIteration == 0)
			$reuseTrees = false;

		if ($reuseTrees)
			$this->maxflowReuseTreesInit();
		else
			$this->maxflowInit();

		$currentNode = Graph::NONE;
		$edge        = Graph::NONE;

		$this->testImage("avant_graphcut",$this->img_name,$this->ext);
		// main loop
		while (true) {

			$activeNode = $currentNode;

			if ($activeNode != Graph::NONE) {
				// remove active flag
				$this->graph->setNextNode($activeNode, Graph::NONE);
				if ($this->graph->getParent($activeNode) == Graph::NONE)
					$activeNode = Graph::NONE;
			}
			if ($activeNode == Graph::NONE) {
				$activeNode = $this->getNextActiveNode();
				if ($activeNode == Graph::NONE)
					// no more active nodes - we're done here
					break; 
			}

			// groth
			if (!$this->graph->getInSink($activeNode)) {
				// grow source tree
				for ($edge = $this->graph->getFirstOutgoing($activeNode); $edge != Graph::NONE; $edge = $this->graph->getNextEdge($edge)) {
					if ($this->graph->getResidualEdgeCapacity($edge) != 0) {

						$headNode = $this->graph->getHead($edge);

						if ($this->graph->getParent($headNode) == Graph::NONE) {
							// free node found, add to source tree
							$this->graph->setInSink($headNode, false);
							$this->graph->setParent($headNode, $this->graph->getSister($edge));
							$this->graph->setTimestamp($headNode, $this->graph->getTimestamp($activeNode));
							$this->graph->setDistance($headNode, $this->graph->getDistance($activeNode) + 1);
							$this->setNodeActive($headNode);
							$this->addToChangedList($headNode);

						} else if ($this->graph->getInSink($headNode)) {
							// node is not free and belongs to other tree - path
							// via edge found
							break;

						} else if ($this->graph->getTimestamp($headNode) <= $this->graph->getTimestamp($activeNode) &&
								$this->graph->getDistance($headNode)  >  $this->graph->getDistance($activeNode)) {
									// node is not free and belongs to our tree - try to
									// shorten its $distance to the source
									$this->graph->setParent($headNode, $this->graph->getSister($edge));
									$this->graph->setTimestamp($headNode, $this->graph->getTimestamp($activeNode));
									$this->graph->setDistance($headNode, $this->graph->getDistance($activeNode) + 1);
								}
					}
				}
			} else {
				// activeNode is in sink, grow sink tree
				for ($edge = $this->graph->getFirstOutgoing($activeNode); $edge != Graph::NONE; $edge = $this->graph->getNextEdge($edge)) {
					if ($this->graph->getResidualEdgeCapacity($this->graph->getSister($edge)) != 0) {

						$headNode = $this->graph->getHead($edge);

						if ($this->graph->getParent($headNode) == Graph::NONE) {
							// free node found, add to sink tree
							$this->graph->setInSink($headNode, true);
							$this->graph->setParent($headNode, $this->graph->getSister($edge));
							$this->graph->setTimestamp($headNode, $this->graph->getTimestamp($activeNode));
							$this->graph->setDistance($headNode, $this->graph->getDistance($activeNode) + 1);
							$this->setNodeActive($headNode);
							$this->addToChangedList($headNode);

						} else if (!$this->graph->getInSink($headNode)) {
							// node is not free and belongs to other tree - path
							// via $edge's sister found
							$edge = $this->graph->getSister($edge);
							break;

						} else if ($this->graph->getTimestamp($headNode) <= $this->graph->getTimestamp($activeNode) &&
								$this->graph->getDistance($headNode)  >  $this->graph->getDistance($activeNode)) {
									// node is not free and belongs to our tree - try to
									// shorten its $distance to the sink
									$this->graph->setParent($headNode, $this->graph->getSister($edge));
									$this->graph->setTimestamp($headNode, $this->graph->getTimestamp($activeNode));
									$this->graph->setDistance($headNode, $this->graph->getDistance($activeNode) + 1);
								}
					}
				}
			}

			$this->time++;

			if ($edge != Graph::NONE) {
				// we found a path via edge
				//$this->testImage($edge);
				// set active flag
				$this->graph->setNextNode($activeNode, $activeNode);
				$currentNode = $activeNode;

				// augmentation
				$this->augment($edge);

				// adoption
				while ($this->orphans->count() > 0) {
					$orphan = $this->orphans->pop();
					if ($this->graph->getInSink($orphan))
						$this->processSinkOrphan($orphan);
					else
						$this->processSourceOrphan($orphan);
				}
			} else {
				// no path found
				$currentNode = Graph::NONE;
			}
		}

		$this->maxflowIteration++;

		// create list of changed nodes
		if ($changedNodes != null) {
			$changedNodes=array();
			for ($i = 0; $i < $this->graph->getNumNodes(); $i++)
				if ($this->graph->getInChangedList($i))
					$changedNodes[] = $i;
		}

		$this->testImage("apres_graphcut",$this->img_name,$this->ext);
		return $this->totalFlow;
	}

	/**
	 * Get the segmentation, i.e., the terminal node that is connected to the
	 * specified node. If there are several min-cut solutions, free nodes are
	 * assigned to the background.
	 *
	 * @param nodeId the node to check
	 * @return Either <tt>Terminal.FOREGROUND</tt> or
	 *         <tt>Terminal.BACKGROUND</tt>
	 */
	public function getTerminal($node) {

		if ($this->graph->getParent($node) != Graph::NONE)
			return $this->graph->getInSink($node) ? Terminal::BACKGROUND : Terminal::FOREGROUND;
		else
			return Terminal::BACKGROUND;
	}

	/**
	 * Gets the number of nodes in this $this->graph->
	 *
	 * @return The number of nodes
	 */
	public function getNumNodes() {
		return $this->graph->getNumNodes();
	}

	/**
	 * Gets the number of edges in this $this->graph->
	 *
	 * @return The number of edges.
	 */
	public function getNumEdges() {
		return $this->graph->getNumEdges();
	}

	/**
	 * Mark a node as being changed.
	 *
	 * Use this method if the $this->graph weights changed after a previous computation
	 * of the max-flow. The next computation will be faster by just considering
	 * changed nodes.
	 *
	 * A node has to be considered changed if any of its adjacent edges changed
	 * its weight.
	 *
	 * @param nodeId The node that changed.
	 */
	public function markNode($node) {

		if ($this->graph->getNextNode($node) == Graph::NONE) {
			if ($this->activeQueueLast[1] != Graph::NONE)
				$this->graph->setNextNode($this->activeQueueLast[1], $node);
			else
				$this->activeQueueFirst[1] = $node;

			$this->activeQueueLast[1] = $node;
			$this->graph->setNextNode($node, $node);
		}

		$this->graph->setMarked($node, true);
	}

	/*
	 * PRIVATE METHODS
	*/

	/**
	 * Marks a node as being active and adds it to second queue of active nodes.
	 */
	private function setNodeActive($node) {

		if ($this->graph->getNextNode($node) == Graph::NONE) {
			if ($this->activeQueueLast[1] != Graph::NONE)
				$this->graph->setNextNode($this->activeQueueLast[1], $node);
			else
				$this->activeQueueFirst[1] = $node;

			$this->activeQueueLast[1] = $node;
			$this->graph->setNextNode($node, $node);
		}
	}

	/**
	 * Gets the next active node, that is, the first node of the first queue of
	 * active nodes. If this queue is empty, the second queue is used. Returns
	 * <tt>nyll</tt>, if no active node is left.
	 */
	private function getNextActiveNode() {

		

		while (true) {

			$node = $this->activeQueueFirst[0];

			if ($node == Graph::NONE) {
				// queue 0 was empty, try other one
				$node = $this->activeQueueFirst[1];

				// swap queues
				$this->activeQueueFirst[0] = $this->activeQueueFirst[1];
				$this->activeQueueLast[0]  = $this->activeQueueLast[1];
				$this->activeQueueFirst[1] = Graph::NONE;
				$this->activeQueueLast[1]  = Graph::NONE;

				// if other queue was emtpy as well, return Graph::NONE
				if ($node == Graph::NONE)
					return Graph::NONE;
			}

			// remove current $node from active list
			if ($this->graph->getNextNode($node) == $node) {
				// this was the last one
				$this->activeQueueFirst[0] = Graph::NONE;
				$this->activeQueueLast[0]  = Graph::NONE;
			} else
				$this->activeQueueFirst[0] = $this->graph->getNextNode($node);

			// not in any list anymore
			$this->graph->setNextNode($node, Graph::NONE);

			// return only if it has a parent and is therefore active
			if ($this->graph->getParent($node) != Graph::NONE)
				return $node;
		}
	}

	/**
	 * Mark a node as orphan and add it to the front of the queue.
	 */
	private function addOrphanAtFront($node) {

		$this->graph->setParent($node, Graph::ORPHAN);

		$this->orphans->unshift($node);
	}

	/**
	 * Mark a node as orphan and add it to the back of the queue.
	 */
	private function addOrphanAtBack($node) {

		$this->graph->setParent($node, Graph::ORPHAN);

		$this->orphans->push($node);
	}

	/**
	 * Add a node to the list of potentially changed nodes.
	 */
	private function addToChangedList($node) {

		$this->graph->setInChangedList($node, true);
	}

	/**
	 * Initialise the algorithm.
	 *
	 * Only called if <tt>reuseTrees</tt> is false.
	 */
	private function maxflowInit() {

		$this->activeQueueFirst[0] = Graph::NONE;
		$this->activeQueueLast[0]  = Graph::NONE;
		$this->activeQueueFirst[1] = Graph::NONE;
		$this->activeQueueLast[1]  = Graph::NONE;

		$this->orphans = new SplDoublyLinkedList();

		$this->time = 0;
        echo "\n numnode = ".$this->getNumNodes();
		for ($node = 0; $node < $this->graph->getNumNodes(); $node++) {

			$this->graph->setNextNode($node, Graph::NONE);
			$this->graph->setMarked($node, false);
			$this->graph->setInChangedList($node, false);
			$this->graph->setTimestamp($node, $this->time);

			if ($this->graph->getResidualNodeCapacity($node) > 0) {
				// $node is connected to source
				//echo "source";
				$this->graph->setInSink($node, false);
				$this->graph->setParent($node, Graph::TERMINAL);
				$this->setNodeActive($node);
				$this->graph->setDistance($node, 1);
			} else if ($this->graph->getResidualNodeCapacity($node) < 0) {
				// $node is connected to sink
				//echo "\n target nodes ";
				$this->graph->setInSink($node, true);
				$this->graph->setParent($node, Graph::TERMINAL);
				$this->setNodeActive($node);
				$this->graph->setDistance($node, 1);
			} else {
				//echo "none";
				$this->graph->setParent($node, Graph::NONE);
			}
		}
	}

	/**
	 * Initialise the algorithm.
	 *
	 * Only called if <tt>reuseTrees</tt> is true.
	 */
	private function maxflowReuseTreesInit() {

		

		$queueStart = $this->activeQueueFirst;

		$this->activeQueueFirst[0] = Graph::NONE;
		$this->activeQueueLast[0]  = Graph::NONE;
		$this->activeQueueFirst[1] = Graph::NONE;
		$this->activeQueueLast[1]  = Graph::NONE;

		$this->orphans = new SplDoublyLinkedList();

		$this->time++;

		while (($node1 = $queueStart) != Graph::NONE) {

			$queueStart = $this->graph->getNextNode($node1);

			if ($queueStart == $node1)
				$queueStart = Graph::NONE;

			$this->graph->setNextNode($node1, Graph::NONE);
			$this->graph->setMarked($node1, false);
			$this->setNodeActive($node1);

			if ($this->graph->getResidualNodeCapacity($node1) == 0) {
				if ($this->graph->getParent($node1) != Graph::NONE)
					$this->addOrphanAtBack($node1);
				continue;
			}

			if ($this->graph->getResidualNodeCapacity($node1) > 0) {

				if ($this->graph->getParent($node1) == Graph::NONE || $this->graph->getInSink($node1)) {

					$this->graph->setInSink($node1, false);
					for ($edge = $this->graph->getFirstOutgoing($node1); $edge != Graph::NONE; $edge = $this->graph->getNextEdge($edge)) {

						$node2 = $this->graph->getHead($edge);
						if (!$this->graph->getMarked($node2)) {
							if ($this->graph->getParent($node2) == $this->graph->getSister($edge))
								$this->addOrphanAtBack($node2);
							if ($this->graph->getParent($node2) != Graph::NONE && $this->graph->getInSink($node2) && $this->graph->getResidualEdgeCapacity($edge) > 0)
								$this->setNodeActive($node2);
						}
					}
					$this->addToChangedList($node1);
				}
			} else {

				if ($this->graph->getParent($node1) == Graph::NONE || !$this->graph->getInSink($node1)) {

					$this->graph->setInSink($node1, true);
					for ($edge = $this->graph->getFirstOutgoing($node1); $edge != Graph::NONE; $edge = $this->graph->getNextEdge($edge)) {

						$node2 = $this->graph->getHead($edge);
						if (!$this->graph->getMarked($node2)) {
							if ($this->graph->getParent($node2) == $this->graph->getSister($edge))
								$this->addOrphanAtBack($node2);
							if ($this->graph->getParent($node2) != Graph::NONE &&
									!$this->graph->getInSink($node2) &&
									$this->graph->getResidualEdgeCapacity($this->graph->getSister($edge)) > 0)
										$this->setNodeActive($node2);
						}
					}
					$this->addToChangedList($node1);
				}
			}
			$this->graph->setParent($node1, Graph::TERMINAL);
			$this->graph->setTimestamp($node1, $this->time);
			$this->graph->setDistance($node1, 1);
		}

		// adoption
		while ($this->orphans->count() > 0) {
			$orphan = $this->orphans->pop();
			if ($this->graph->getInSink($orphan))
				$this->processSinkOrphan($orphan);
			else
				$this->processSourceOrphan($orphan);
		}
	}

	/**
	 * Perform the augmentation step of the $this->graph cut algorithm.
	 *
	 * This is done whenever a path between the source and the sink was found.
	 */
	private function augment($middle) {

		

		

		// 1. find bottleneck capacity

		// 1a - the source tree
		$bottleneck = $this->graph->getResidualEdgeCapacity($middle);
		for ($node = $this->graph->getHead($this->graph->getSister($middle)); ; $node = $this->graph->getHead($edge)) {

			$edge = $this->graph->getParent($node);

			if ($edge == Graph::TERMINAL)
				break;
			if ($bottleneck > $this->graph->getResidualEdgeCapacity($this->graph->getSister($edge)))
				$bottleneck = $this->graph->getResidualEdgeCapacity($this->graph->getSister($edge));
		}

		if ($bottleneck > $this->graph->getResidualNodeCapacity($node))
			$bottleneck = $this->graph->getResidualNodeCapacity($node);

		// 1b - the sink tree
		for ($node = $this->graph->getHead($middle); ; $node = $this->graph->getHead($edge)) {

			$edge = $this->graph->getParent($node);

			if ($edge == Graph::TERMINAL)
				break;
			if ($bottleneck > $this->graph->getResidualEdgeCapacity($edge))
				$bottleneck = $this->graph->getResidualEdgeCapacity($edge);
		}
		if ($bottleneck > -$this->graph->getResidualNodeCapacity($node))
			$bottleneck = -$this->graph->getResidualNodeCapacity($node);

		// 2. augmenting

		// 2a - the source tree
		$this->graph->setResidualEdgeCapacity($this->graph->getSister($middle), $this->graph->getResidualEdgeCapacity($this->graph->getSister($middle)) + $bottleneck);
		$this->graph->setResidualEdgeCapacity($middle, $this->graph->getResidualEdgeCapacity($middle) - $bottleneck);
		for ($node = $this->graph->getHead($this->graph->getSister($middle)); ; $node = $this->graph->getHead($edge)) {

			$edge = $this->graph->getParent($node);

			if ($edge == Graph::TERMINAL) {
				// end of path
				break;
			}
			$this->graph->setResidualEdgeCapacity($edge, $this->graph->getResidualEdgeCapacity($edge) + $bottleneck);
			$this->graph->setResidualEdgeCapacity($this->graph->getSister($edge), $this->graph->getResidualEdgeCapacity($this->graph->getSister($edge)) - $bottleneck);
			if ($this->graph->getResidualEdgeCapacity($this->graph->getSister($edge)) == 0)
				$this->addOrphanAtFront($node);
		}
		$this->graph->setResidualNodeCapacity($node, $this->graph->getResidualNodeCapacity($node) - $bottleneck);
		if ($this->graph->getResidualNodeCapacity($node) == 0)
			$this->addOrphanAtFront($node);

		// 2b - the sink tree
		for ($node = $this->graph->getHead($middle); ; $node = $this->graph->getHead($edge)) {

			$edge = $this->graph->getParent($node);

			if ($edge == Graph::TERMINAL) {
				// end of path
				break;
			}
			$this->graph->setResidualEdgeCapacity($this->graph->getSister($edge), $this->graph->getResidualEdgeCapacity($this->graph->getSister($edge)) + $bottleneck);
			$this->graph->setResidualEdgeCapacity($edge, $this->graph->getResidualEdgeCapacity($edge) - $bottleneck);
			if ($this->graph->getResidualEdgeCapacity($edge) == 0)
				$this->addOrphanAtFront($node);
		}
		$this->graph->setResidualNodeCapacity($node, $this->graph->getResidualNodeCapacity($node) + $bottleneck);
		if ($this->graph->getResidualNodeCapacity($node) == 0)
			$this->addOrphanAtFront($node);

		$this->totalFlow += $bottleneck;
	}

	/**
	 * Adopt an orphan.
	 */
	private function processSourceOrphan($orphan) {

		$bestEdge    = Graph::NONE;
		$minDistance = INF;

		for ($orphanEdge = $this->graph->getFirstOutgoing($orphan); $orphanEdge != Graph::NONE; $orphanEdge = $this->graph->getNextEdge($orphanEdge))
			if ($this->graph->getResidualEdgeCapacity($this->graph->getSister($orphanEdge)) != 0) {

				$node       = $this->graph->getHead($orphanEdge);
				$parentEdge = $this->graph->getParent($node);

				if (!$this->graph->getInSink($node) && $parentEdge != Graph::NONE) {

					// check the origin of node
					$distance = 0;
					while (true) {

						if ($this->graph->getTimestamp($node) == $this->time) {
							$distance += $this->graph->getDistance($node);
							break;
						}
						$parentEdge = $this->graph->getParent($node);
						$distance++;
						if ($parentEdge == Graph::TERMINAL) {
							$this->graph->setTimestamp($node, $this->time);
							$this->graph->setDistance($node, 1);
							break;
						}
						if ($parentEdge == Graph::ORPHAN) {
							$distance = INF;
							break;
						}
						// otherwise, proceed to the next $node
						$node = $this->graph->getHead($parentEdge);
					}
					if ($distance < INF) { // $node originates from the source

						if ($distance < $minDistance) {
							$bestEdge    = $orphanEdge;
							$minDistance = $distance;
						}
						// set marks along the path
						for ($node = $this->graph->getHead($orphanEdge);
						$this->graph->getTimestamp($node) != $this->time;
						$node = $this->graph->getHead($this->graph->getParent($node))) {

							$this->graph->setTimestamp($node, $this->time);
							$this->graph->setDistance($node, $distance);
							$distance--;
						}
					}
				}
			}

		$this->graph->setParent($orphan, $bestEdge);
		if ($bestEdge != Graph::NONE) {
			$this->graph->setTimestamp($orphan, $this->time);
			$this->graph->setDistance($orphan, $minDistance + 1);
		} else {
			// no parent found
			$this->addToChangedList($orphan);

			// process neighbors
			for ($orphanEdge = $this->graph->getFirstOutgoing($orphan); $orphanEdge != Graph::NONE; $orphanEdge = $this->graph->getNextEdge($orphanEdge)) {

				$node = $this->graph->getHead($orphanEdge);
				$parentEdge = $this->graph->getParent($node);
				if (!$this->graph->getInSink($node) && $parentEdge != Graph::NONE) {

					if ($this->graph->getResidualEdgeCapacity($this->graph->getSister($orphanEdge)) != 0)
						$this->setNodeActive($node);
					if ($parentEdge != Graph::TERMINAL && $parentEdge != Graph::ORPHAN && $this->graph->getHead($parentEdge) == $orphan)
						$this->addOrphanAtBack($node);
				}
			}
		}

	}

	
	/**
	 * Adopt an orphan.
	 */
	private function processSinkOrphan($orphan) {

		$bestEdge    = Graph::NONE;
		$minDistance = INF;

		for ($orphanEdge = $this->graph->getFirstOutgoing($orphan); $orphanEdge != Graph::NONE; $orphanEdge = $this->graph->getNextEdge($orphanEdge))
			if ($this->graph->getResidualEdgeCapacity($orphanEdge) != 0) {

				$node       = $this->graph->getHead($orphanEdge);
				$parentEdge = $this->graph->getParent($node);

				if ($this->graph->getInSink($node) && $parentEdge != Graph::NONE) {

					// check the origin of $node
					$distance = 0;
					while (true) {

						if ($this->graph->getTimestamp($node) == $this->time) {
							$distance += $this->graph->getDistance($node);
							break;
						}
						$parentEdge = $this->graph->getParent($node);
						$distance++;
						if ($parentEdge == Graph::TERMINAL) {
							$this->graph->setTimestamp($node, $this->time);
							$this->graph->setDistance($node, 1);
							break;
						}
						if ($parentEdge == Graph::ORPHAN) {
							$distance = INF;
							break;
						}
						// otherwise, proceed to the next $node
						$node = $this->graph->getHead($parentEdge);
					}
					if ($distance < INF) {
						// $node originates from the sink
						if ($distance < $minDistance) {
							$bestEdge    = $orphanEdge;
							$minDistance = $distance;
						}
						// set marks along the path
						for ($node = $this->graph->getHead($orphanEdge);
						$this->graph->getTimestamp($node) != $this->time;
						$node = $this->graph->getHead($this->graph->getParent($node))) {

							$this->graph->setTimestamp($node, $this->time);
							$this->graph->setDistance($node, $distance);
							$distance--;
						}
					}
				}
			}

		$this->graph->setParent($orphan, $bestEdge);
		if ($bestEdge != Graph::NONE) {
			$this->graph->setTimestamp($orphan, $this->time);
			$this->graph->setDistance($orphan, $minDistance + 1);
		} else {
			// no parent found
			$this->addToChangedList($orphan);

			// process neighbors
			for ($orphanEdge = $this->graph->getFirstOutgoing($orphan); $orphanEdge != Graph::NONE; $orphanEdge = $this->graph->getNextEdge($orphanEdge)) {

				$node = $this->graph->getHead($orphanEdge);
				$parentEdge = $this->graph->getParent($node);
				if ($this->graph->getInSink($node) && $parentEdge != Graph::NONE) {

					if ($this->graph->getResidualEdgeCapacity($orphanEdge) != 0)
						$this->setNodeActive($node);
					if ($parentEdge != Graph::TERMINAL && $parentEdge != Graph::ORPHAN && $this->graph->getHead($parentEdge) == $orphan)
						$this->addOrphanAtBack($node);
				}
			}
		}
	}
}

?>