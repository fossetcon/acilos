<?php

/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines a method for rebooting any host amazon instances to free up memory from the clutches of java
**
** $QT_BEGIN_LICENSE:LGPL$
**
** GNU Lesser General Public License Usage
** Alternatively, this file may be used under the terms of the GNU Lesser
** General Public License version 2.1 as published by the Free Software
** Foundation and appearing in the file LICENSE.LGPL included in the
** packaging of this file.  Please review the following information to
** ensure the GNU Lesser General Public License version 2.1 requirements
** will be met: http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
**
**
** If you have questions regarding the use of this file, please contact
** Omnibond Systems -  www.omnibond.com
**
** $QT_END_LICENSE$
*/

require '../../vendor/autoload.php';

use \Aws\Ec2\Ec2Client;

$id = file_get_contents('http://169.254.169.254/latest/meta-data/instance-id');

$credArr = array(
	'key'    => 'AKIAJS2QEUMYI7DKZYUA',
	'secret' => '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',
	'region' => 'us-east-1'
);
//create the client
$client = Ec2Client::factory($credArr);

$result = $client->rebootInstances(array(
	'InstanceIds' => array($id)
));

?>