/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines the titebar widget
** 
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
define(['dojo/_base/declare',
		'dojo/_base/lang',
		'dojo/dom-construct',
		'dojo/keys',
		'dojo/on',
		'dijit/_WidgetBase'
], function(declare, lang, domConstruct, keys, on, WidgetBase) {
	return declare([WidgetBase], {
		baseClass: "titleBar",
		buttons: [],
		
		buildRendering: function() {
			this.inherited(arguments);

			if(this.buttons.length > 0){
				for(var x = 0; x < this.buttons.length; x++){
					this.buttons[x].placeAt(this.domNode);
				}
			}
		}
			
	});
});