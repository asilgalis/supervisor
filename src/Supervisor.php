<?php

namespace Supervisor;

/**
 * Supervisor API.
 *
 * Status and Control
 * @method string  getAPIVersion()
 * @method string  getSupervisorVersion()
 * @method string  getIdentification()
 * @method array   getState()
 * @method int getPID()
 * @method string  readLog(integer $offset, integer $length)
 * @method bool clearLog()
 * @method bool shutdown()
 * @method bool restart()
 *
 * Process Control
 * @method array   getProcessInfo(string $name)
 * @method array   getAllProcessInfo()
 * @method bool startProcess(string $name, boolean $wait = true)
 * @method bool startAllProcesses(boolean $wait = true)
 * @method bool startProcessGroup(string $name, boolean $wait = true)
 * @method bool stopProcess(string $name, boolean $wait = true)
 * @method bool stopProcessGroup(string $name, boolean $wait = true)
 * @method bool stopAllProcesses(boolean $wait = true)
 * @method bool signalProcess(string $name, string $signal)
 * @method array signalProcessGroup(string $name, string $signal)
 * @method array signalAllProcesses(string $signal)
 * @method bool sendProcessStdin(string $name, string $chars)
 * @method bool sendRemoteCommEvent(string $type, string $data)
 * @method bool reloadConfig()
 * @method bool addProcessGroup(string $name)
 * @method bool removeProcessGroup(string $name)
 *
 * Process Logging
 * @method string  readProcessStdoutLog(string $name, integer $offset, integer $length)
 * @method string  readProcessStderrLog(string $name, integer $offset, integer $length)
 * @method string  tailProcessStdoutLog(string $name, integer $offset, integer $length)
 * @method string  tailProcessStderrLog(string $name, integer $offset, integer $length)
 * @method bool clearProcessLogs(string $name)
 * @method bool clearAllProcessLogs()
 *
 * @link   http://supervisord.org/api.html
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Supervisor
{
    /**
     * Service states.
     */
    const SHUTDOWN = -1;
    const RESTARTING = 0;
    const RUNNING = 1;
    const FATAL = 2;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Checks if a connection is present.
     *
     * It is done by sending a bump request to the server and catching any thrown exceptions
     *
     * @return bool
     */
    public function isConnected()
    {
        try {
            $this->connector->call('system', 'listMethods');
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Calls a method.
     *
     * @param string $namespace
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function call($namespace, $method, array $arguments = [])
    {
        return $this->connector->call($namespace, $method, $arguments);
    }

    /**
     * Magic __call method.
     *
     * Handles all calls to supervisor namespace
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->connector->call('supervisor', $method, $arguments);
    }

    /**
     * Is service running?
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->checkState(self::RUNNING);
    }

    /**
     * Checks if supervisord is in given state.
     *
     * @param int $checkState
     *
     * @return bool
     */
    public function checkState($checkState)
    {
        $state = $this->getState();

        return $state['statecode'] == $checkState;
    }

    /**
     * Returns all processes as Process objects.
     *
     * @return array Array of Process objects
     */
    public function getAllProcesses()
    {
        $processes = $this->getAllProcessInfo();

        foreach ($processes as $key => $processInfo) {
            $processes[$key] = new Process($processInfo);
        }

        return $processes;
    }

    /**
     * Returns a specific Process.
     *
     * @param string $name Process name or 'group:name'
     *
     * @return Process
     */
    public function getProcess($name)
    {
        $process = $this->getProcessInfo($name);

        return new Process($process);
    }

    /**
     * Return an array listing the available method names.
     *
     * @return array An array of method names available (strings)
     */
    public function listMethods()
    {
        return $this->connector->call('system', 'listMethods');
    }

    /**
     * Return a string showing the method’s documentation.
     *
     * @param string $name The name of the method
     *
     * @return string The documentation for the method name
     */
    public function methodHelp($name)
    {
        return $this->connector->call('system', 'methodHelp', [$name]);
    }

    /**
     * Return an array describing the method signature in the form [rtype, ptype, ptype...]
     * where rtype is the return data type of the method,
     * and ptypes are the parameter data types that the method accepts in method argument order.
     *
     * @param string $name The name of the method
     *
     * @return array The result
     */
    public function methodSignature($name)
    {
        return $this->connector->call('system', 'methodSignature', [$name]);
    }

    /**
     * Process an array of calls, and return an array of results.
     * Calls should be structs of the form {‘methodName’: string, ‘params’: array}.
     * Each result will either be a single-item array containing the result value,
     * or a struct of the form {‘faultCode’: int, ‘faultString’: string}.
     * This is useful when you need to make lots of small calls without lots of round trips.
     *
     * @param array $calls An array of call requests
     *
     * @return array result An array of results
     */
    public function multicall(array $calls)
    {
        return $this->connector->call('system', 'multicall', [$calls]);
    }
}
