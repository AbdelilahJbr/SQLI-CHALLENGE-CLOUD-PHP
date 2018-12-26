<?php
    class Store {
        private $name;
        private $files;
        private $storeUsedDisk;
        
        public function __construct($name) {
            $this->name = $name;
            $this->files = array();
            $this->storeUsedDisk = 0;
        }
        
        public function addFile($file) {
            $this->files[] = $file;
            $this->storeUsedDisk += 0.1;
        }
        
        public function listFiles() {
            return !(count($this->files) > 0) ? "empty" : implode(", ",$this->files);
        }
        
        public function getStoreMemory() {
            return $this->storeUsedDisk;
        }
    }
    
    abstract class MachineState {
        const inactive = "inactive";
        const running = "running";
        const stopped = "stopped";
    }
    
    class Machine {
        private $name, $operatingSystem, $diskSize, $memory, $state;
        
        public function __construct($name, $operatingSystem, $diskSize, $memory) {
            $this->name = $name;
            $this->operatingSystem = $operatingSystem;
            $this->diskSize = $diskSize;
            $this->memory = $memory;
            $this->state = MachineState::inactive;
        }
        
        public function getState() {
            return $this->state;
        }
        
        public function setState($state) {
            $this->state = $state;
        }
        
        public function getDiskSizeValue() {
            return $this->diskSize;
        }
        
        public function getMemoryValue(){
            return $this->memory;
        }
        
    }
    
    class CreateStoreException extends RuntimeException {
        public function __construct(){
            parent::__construct();
        }
    }
    
    class MachineStateException extends RuntimeException {
        public function __construct(){
            parent::__construct();
        }
    }
    
    class CloudInfrastructure {
        private $stores = array();
        private $machines = array();
        
        public function createStore($storeName) {
            if (!array_key_exists($storeName, $this->stores))
                $this->stores[$storeName] = new Store($storeName);
            else throw new CreateStoreException();
        }
        
        public function uploadDocument($storeName, String... $files) {
            foreach ($files as $file){
                $this->stores[$storeName]->addFile($file);
            }
        }
        
        public function listStores(){
            $listStore = "";
            foreach(array_keys($this->stores) as $key){
                $listStore .= $key . ":" . $this->stores[$key]->listFiles()."||";
            }
            return substr($listStore, 0, strlen($listStore)-2);
        }
        
        public function deleteStore($storeName){
            unset($this->stores[$storeName]);
        }
        
        public function emptyStore($storeName){
            $this->stores[$storeName] = new Store($storeName);
        }
        
        public function createMachine($name, $operatingSystem, $diskSize, $memory){
            $this->machines[$name] = new Machine($name, $operatingSystem, $diskSize, $memory);
        }
        
        public function listMachine(){
            $mListMachines = "";
            foreach(array_keys($this->machines) as $key){
                $mListMachines .= $key . ":" . $this->machines[$key]->getState() . "||";    
            }
            return substr($mListMachines, 0, strlen($mListMachines)-2);
        }
        
        public function startMachine($machineName){
            if ($this->machines[$machineName]->getState() != MachineState::running)
                $this->machines[$machineName]->setState(MachineState::running);
            else throw new MachineStateException();
        }
        
        public function stopMachine($machineName){
            $this->machines[$machineName]->setState(MachineState::stopped);
        }
        
        public function usedMemory($machineName){
            $machine = $this->machines[$machineName];
            return ($machine->getState == MachineState::running) ? $machine->getMemoryValue() : 0;
        }
        
        public function machineUsedDisk($machineName){
            return $this->machines[$machineName]->getDiskSizeValue();
        }
        
        public function storeUsedDisk($storeName){
            return $this->stores[$storeName]->getStoreMemory();
        }
        
        public function usedDidk($name){
            return (array_key_exists($name, $this->stores)) ? storeUsedDisk($name) : machineUsedDisk($name);
        }
        
        public function globalUsedDisk(){
            $usedDisk = 0.0;
            foreach(array_keys($this->stores) as $key){
                $usedDisk += storeUsedDisk($key);
            }
            foreach(array_keys($this->machines) as $key){
                $usedDisk += machineUsedDisk($key);
            }
            return $usedDisk;
        }
        
        public function globalUsedMemory(){
            $globalUsedMemory = 0.0;
            foreach(array_keys($this->machines) as $key){
                $globalUsedMemory += usedMemory($key);
            }
            return $globalUsedMemory;
        }
    }