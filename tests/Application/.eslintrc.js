/*
 * This file is part of the SyliusBillogramPlugin.
 *
 * Copyright (c) debricked AB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

module.exports = {
    extends: 'airbnb-base',
    env: {
        node: true,
    },
    rules: {
        'object-shorthand': ['error', 'always', {
            avoidQuotes: true,
            avoidExplicitReturnArrows: true,
        }],
        'function-paren-newline': ['error', 'consistent'],
        'max-len': ['warn', 120, 2, {
            ignoreUrls: true,
            ignoreComments: false,
            ignoreRegExpLiterals: true,
            ignoreStrings: true,
            ignoreTemplateLiterals: true,
        }],
    },
};