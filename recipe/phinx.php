<?php

namespace Deployer;

/**
 * Phinx recipe for Deployer
 *
 * @author    Alexey Boyko <ket4yiit@gmail.com>
 * @contributor Security-Database <info@security-database.com>
 * @copyright 2016 Alexey Boyko 
 * @license   MIT https://github.com/deployphp/recipes/blob/master/LICENSE
 *
 * @link https://github.com/deployphp/recipes
 *
 * @see http://deployer.org
 * @see https://phinx.org
 */

/**
 * Get Phinx command
 *
 * @return Path to Phinx
 */
set('bin/phinx', function () {
        $isExistsCmd = 'if [ -f %s ]; then echo true; fi';

        try {
            $phinxPath = run('which phinx')->toString();
        } catch (\Deployer\Exception\RuntimeException $e) {
            $phinxPath = null;
        }

        if ($phinxPath !== null) {
            return "phinx";
        } else if (run(
            sprintf(
                $isExistsCmd,
                '{{release_path}}/vendor/bin/phinx'
            )
        )->toBool()
        ) {
            return "{{release_path}}/vendor/bin/phinx";
        } else if (run(
            sprintf(
                $isExistsCmd,
                '~/.composer/vendor/bin/phinx'
            )
        )->toBool()
        ) {
            return '~/.composer/vendor/bin/phinx';
        } else {
            throw new \RuntimeException(
                'Cannot find phinx. 
            Please specify path to phinx manually'
            );
        }
    }
);

/**
 * Make Phinx command from env options 
 * 
 * @param string $cmdName Name of command
 * @param array  $conf    Command options(config)
 *
 * @return string Phinx command to execute
 */
set('phinx_get_cmd', function () {
    return function ($cmdName, $conf) {
        $phinx = get('phinx_path') ?: get('bin/phinx');
        
        $phinxCmd = "$phinx $cmdName";

        $options = '';

        foreach ($conf as $name => $value) {
            $options .= " --$name $value";
        }

        $phinxCmd .= $options;

        return $phinxCmd;
    };
});

/**
 * Returns options array that allowed for command
 *
 * @param array $allowedOptions List of allowed options
 *
 * @return array Array of options
 */
set('phinx_get_allowed_config', function () {
    return function ($allowedOptions) {
        $opts = [];

        try { 
            foreach (get('phinx') as $key => $val) {
                if (in_array($key, $allowedOptions)) {
                    $opts[$key] = $val;
                }
            }
        } catch (\RuntimeException $e) {
        }

        return $opts;
    };
});

desc('Migrating database with phinx');
task('phinx:migrate', function () {
        $ALLOWED_OPTIONS = [
            'configuration',
            'date',
            'environment',
            'target',
            'parser'
        ];

        $conf = get('phinx_get_allowed_config')($ALLOWED_OPTIONS); 

        cd('{{release_path}}');
        
        $phinxCmd = get('phinx_get_cmd')('migrate', $conf);

        run($phinxCmd);

        cd('{{deploy_path}}');
    }
);

desc('Rollback database migrations with phinx');
task('phinx:rollback', function () {
        $ALLOWED_OPTIONS = [
            'configuration',
            'date',
            'environment',
            'target',
            'parser'
        ];

        $conf = get('phinx_get_allowed_config')($ALLOWED_OPTIONS); 

        cd('{{release_path}}');

        $phinxCmd = get('phinx_get_cmd')('rollback', $conf);

        run($phinxCmd);        

        cd('{{deploy_path}}');
    }
);

desc('Seed database with phinx');
task('phinx:seed', function () {
        $ALLOWED_OPTIONS = [
            'configuration',
            'environment',
            'parser',
            'seed'
        ];

        $conf = get('phinx_get_allowed_config')($ALLOWED_OPTIONS); 

        cd('{{release_path}}');

        $phinxCmd = get('phinx_get_cmd')('seed:run', $conf);

        run($phinxCmd);

        cd('{{deploy_path}}');
    }
);

desc('Set a migrations breakpoint with phinx');
task('phinx:breakpoint', function () {
        $ALLOWED_OPTIONS = [
            'configuration',
            'environment',
            'remove-all',
            'target'
        ];

        $conf = get('phinx_get_allowed_config')($ALLOWED_OPTIONS); 

        cd('{{release_path}}');

        $phinxCmd = get('phinx_get_cmd')('breakpoint', $conf);

        run($phinxCmd);

        cd('{{deploy_path}}');
    }
);
