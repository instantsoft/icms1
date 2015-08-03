/*
 * Copyright (c) 2008 Tom Coote (http://www.tomcoote.co.uk)
 * This is licensed under GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Add text box's at the top of columns in a table to allow 
 * fast flltering of rows underneath each column.
 * 
 *
 * @name columnFilters
 * @type jQuery
 * @param Object settings;
 *			wildCard	:	A character to be used as a match all wildcard when searching, Leave empty for no wildcards.
 *			notCharacter	:	The character to use at the start of any search text to specify that the results should NOT contain the following text.
 *			caseSensitive	:	True if the filter search's are to be case sensitive.
 *			minSearchCharacters	:	The minimum amount of characters that each search string must contain before the search is applied.
 *			excludeColumns	:	A zero indexed array of column numbers to not allow filtering on.
 *			alternateRowClassNames	:	An array of alternating class names for each row returned after filtering in order to keep odd and even row styling on tables. Maximum of two items.
 *			underline	:	Set to true if the script is to underline the search text whilst filtering is in progress... good for tables with lots of rows where the filter may take a second. (not in Opera)
 *
 * @author Tom Coote (www.tomcoote.co.uk)
 * @version 1.1.0
 */

(function($){

	$.fn.columnFilters = function(settings) {
		var defaults = {  
			wildCard: "*",  
			notCharacter: "!",
			caseSensitive: false,
			minSearchCharacters: 1,
			excludeColumns: [],
			alternateRowClassNames: [],
			underline: false
			};  
		settings = $.extend(defaults, settings);  
	
		return this.each(function() {
		
			function regexEscape(txt, omit) {
				var specials = ['/', '.', '*', '+', '?', '|',
								'(', ')', '[', ']', '{', '}', '\\'];
				
				if (omit) {
					for (var i=0; i < specials.length; i++) {
						if (specials[i] === omit) { specials.splice(i,1); }
					}
				}
				
				var escapePatt = new RegExp('(\\' + specials.join('|\\') + ')', 'g');
				return txt.replace(escapePatt, '\\$1');
			}
		
			var obj = $(this),
				filterRow = document.createElement('tr'),
				wildCardPatt = new RegExp(regexEscape(settings.wildCard || ''),'g'),
				filter;
		
			function addClassToColumn(iColNum, sClassName) {
				$('tbody:first tr', obj).each(
					function() {
						$('td', this).each(
							function(iCellCount) {
								if (iCellCount === iColNum) {
									$(this).addClass(sClassName);
								}
							});
					}
				);
			}
		
			function runFilters(event) {			
				$('input._filterText', obj).each(
					function(iColCount) {
//						var sFilterTxt = (!settings.wildCard) ? regexEscape(this.value) : regexEscape(this.value, settings.wildCard).replace(wildCardPatt, '.*'),
						var sFilterTxt = this.value,
							bMatch = true, 
							sFirst = settings.alternateRowClassNames[0] || '',
							sSecound = settings.alternateRowClassNames[1] || '',
							bOddRow = true;
						
//						if (settings.notCharacter && sFilterTxt.indexOf(settings.notCharacter) === 0) {
//							sFilterTxt = sFilterTxt.substr(settings.notCharacter.length,sFilterTxt.length);
//							if (sFilterTxt.length > 0) { bMatch = false; }
//						}
//						if (sFilterTxt.length < settings.minSearchCharacters) {
//							sFilterTxt = '';
//						}
//						sFilterTxt = sFilterTxt || '.*';
//						sFilterTxt = settings.wildCard ? '^' + sFilterTxt : sFilterTxt;
						var filterPatt = settings.caseSensitive ? new RegExp(sFilterTxt) : new RegExp(sFilterTxt,"i");
						
						$('tbody:first tr', obj).each(
							function() {
								$('td',this).each(
									function(iCellCount) {
										if (iCellCount === iColCount) {
											var sVal = $(this).text().replace(/(\n)|(\r)/ig,'').replace(/\s\s/ig,' ').replace(/^\s/ig,'');
											$(this).removeClass('_match');
											if (filterPatt.test(sVal) === bMatch) {
												$(this).addClass('_match');
											}
										}
									}
								);
								
								if ($('td',this).length !== $('td._match',this).length) {
									$(this).css('display','none');
								}
								else {
									$(this).css('display','');
									if (settings.alternateRowClassNames && settings.alternateRowClassNames.length) {
										$(this).removeClass(sFirst).removeClass(sSecound).addClass((bOddRow) ? sFirst : sSecound);
										bOddRow = !bOddRow;
									}
								}
							}
						);
						
						if (settings.underline) {
							$(this).css('text-decoration','');
						}
					}
				);
			}
			
			function genAlternateClassNames() {
				if (settings.alternateRowClassNames && settings.alternateRowClassNames.length) {
					var sFirst = settings.alternateRowClassNames[0] || '',
						sSecound = settings.alternateRowClassNames[1] || '',
						bOddRow = true;
					
					$('tbody:first tr', obj).each(
						function() {
							if ($(this).css('display') !== 'none') {
								$(this).removeClass(sFirst).removeClass(sSecound);
								$(this).addClass((bOddRow) ? sFirst : sSecound);
								bOddRow = !bOddRow;
							}
						}
					);
				}
			}
				
			$('tbody:first tr:first td', obj).each(
				function(iColCount) {
					var filterColumn = document.createElement('td'),
						filterBox = document.createElement('input');
					
					$(filterBox).attr('type','text').attr('id','_filterText' + iColCount).addClass('_filterText');
					$(filterBox).keyup(
						function() { 
							clearTimeout(filter); 
							filter = setTimeout(runFilters, $('tbody:first tr', obj).length*2);
							if (settings.underline) {
								$(filterBox).css('text-decoration','underline');
							}
						}
					);
					$(filterColumn).append(filterBox);
					$(filterRow).append(filterColumn);
					
					if (settings.excludeColumns && settings.excludeColumns.length) {
						for (var i=0; i < settings.excludeColumns.length; i++) {
							if (settings.excludeColumns[i] === iColCount) {
								$(filterBox).css('visibility','hidden');
							}
						}
					}
					
					addClassToColumn(iColCount, '_filterCol' + iColCount);
				}
			);
				
			$(filterRow).addClass('filterColumns');
			$('thead:first', obj).append(filterRow);
			genAlternateClassNames();
			settings.notCharacter = regexEscape(settings.notCharacter || '');
		});
	};

})(jQuery);