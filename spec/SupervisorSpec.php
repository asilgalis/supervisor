<?php

namespace spec\Supervisor;

use PhpSpec\ObjectBehavior;
use Supervisor\Connector;

class SupervisorSpec extends ObjectBehavior
{
    function let(Connector $connector)
    {
        $this->beConstructedWith($connector);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Supervisor\Supervisor');
    }

    function it_checks_connection(Connector $connector)
    {
        $connector->call('system', 'listMethods')->willReturn('response');

        $this->isConnected()->shouldReturn(true);

        $connector->call('system', 'listMethods')->willThrow('Exception');

        $this->isConnected()->shouldReturn(false);
    }

    function it_calls_a_method(Connector $connector)
    {
        $connector->call('namespace', 'method', [])->willReturn('response');

        $this->call('namespace', 'method')->shouldReturn('response');
    }

    function it_checks_if_supervisor_is_running(Connector $connector)
    {
        $connector->call('supervisor', 'getState', [])->willReturn(['statecode' => 1]);

        $this->isRunning()->shouldReturn(true);
    }

    function it_checks_supervisor_state(Connector $connector)
    {
        $connector->call('supervisor', 'getState', [])->willReturn(['statecode' => 1]);

        $this->checkState(1)->shouldReturn(true);
    }

    function it_returns_all_processes(Connector $connector)
    {
        $connector->call('supervisor', 'getAllProcessInfo', [])->willReturn([
            [
                'name' => 'process_name'
            ]
        ]);

        $processes = $this->getAllProcesses();

        $processes->shouldBeArray();
        $processes[0]->shouldHaveType('Supervisor\Process');
        $processes[0]->getName()->shouldReturn('process_name');
    }

    function it_returns_a_process_(Connector $connector)
    {
        $connector->call('supervisor', 'getProcessInfo', ['process_name'])->willReturn(['name' => 'process_name']);

        $process = $this->getProcess('process_name');

        $process->shouldHaveType('Supervisor\Process');
        $process->getName()->shouldReturn('process_name');
    }

    function it_returns_a_list_of_methods_(Connector $connector)
    {
        $connector->call('system', 'listMethods')->willReturn(['list', 'of', 'methods']);

        $this->listMethods()->shouldReturn(['list', 'of', 'methods']);
    }

    function it_returns_method_help_(Connector $connector)
    {
        $connector->call('system', 'methodHelp', ['method_name'])->willReturn('method help');

        $this->methodHelp('method_name')->shouldReturn('method help');
    }

    function it_returns_method_signature_(Connector $connector)
    {
        $connector->call('system', 'methodSignature', ['method_name'])->willReturn(['method', 'signature']);

        $this->methodSignature('method_name')->shouldReturn(['method', 'signature']);
    }

    function it_processes_a_multicall_(Connector $connector)
    {
        $connector->call('system', 'multicall', [['multiple', 'calls']])->willReturn(['list', 'of', 'results']);

        $this->multicall(['multiple', 'calls'])->shouldReturn(['list', 'of', 'results']);
    }
}
