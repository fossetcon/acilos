/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
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
/****************************************************************************
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems: www.omnibond.com for Acilos.com
**
** OpenClemson Dojo-Mama framework: https://github.com/OpenClemson/dojo-mama
** Copyright (C) 2014 Clemson University and/or its subsidiary(-ies).
** All rights reserved.
** Clemson University - www.clemson.edu
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
** If you have questions regarding the use of this file, please contact
** Omnibond Systems - www.omnibond.com
** Clemson University - www.clemson.edu
**
** $QT_END_LICENSE$
*/
define(['dojo/_base/declare',
		'dojox/mobile/EdgeToEdgeList',
		'dojo-mama/util/LinkListItem',
		'dojo-mama/views/ModuleScrollableView'
], function(declare, EdgeToEdgeList, LinkListItem, ModuleScrollableView) {

	// module:
	//     dojo-mama/views/ModuleList

	return declare([ModuleScrollableView], {
		// summary:
		//     A module list
		postCreate: function() {
			// summary:
			//     Construct the UI for this widget, setting this.domNode
			// tags:
			//     protected

			this.inherited(arguments);

			var li, m;
			this.list = new EdgeToEdgeList();
			this.list.startup();
			for (m in this.modules) {
				if (this.modules.hasOwnProperty(m)) {
					li = new LinkListItem({
						text: this.modules[m].label,
						href: '#/' + m
					});
					this.list.addChild(li);
				}
			}
			this.list.placeAt(this.domNode);
		}
	});
});
