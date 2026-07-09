import { PageFlip } from '../vendor/page-flip/page-flip.module.js';
import * as pdfjsLib from '../vendor/pdfjs/pdf.min.js';

(function () {
	'use strict';

	const defaultSettings = Object.assign({
		workerSrc: new URL('../vendor/pdfjs/pdf.worker.min.js', import.meta.url).href,
		loading: 'Loading PDF...',
		loadingPage: 'Loading page %d',
		pageStatus: 'Page %1$d of %2$d',
		pageLabel: 'Page %1$d of %2$d',
		viewerError: 'Unable to open PDF.',
		searchReady: 'Search PDF',
		searchIndexing: 'Searching %1$d/%2$d pages',
		searchResults: 'Result %1$d of %2$d',
		searchNoResults: 'No results',
	}, window.lfkPdfViewer || {});

	const getViewerSettings = function (viewer) {
		const data = viewer.dataset || {};

		return Object.assign({}, defaultSettings, {
			workerSrc: data.pdfWorkerSrc || defaultSettings.workerSrc,
			loading: data.pdfLoading || defaultSettings.loading,
			loadingPage: data.pdfLoadingPage || defaultSettings.loadingPage,
			pageStatus: data.pdfPageStatus || defaultSettings.pageStatus,
			pageLabel: data.pdfPageLabel || defaultSettings.pageLabel,
			viewerError: data.pdfViewerError || defaultSettings.viewerError,
			searchReady: data.pdfSearchReady || defaultSettings.searchReady,
			searchIndexing: data.pdfSearchIndexing || defaultSettings.searchIndexing,
			searchResults: data.pdfSearchResults || defaultSettings.searchResults,
			searchNoResults: data.pdfSearchNoResults || defaultSettings.searchNoResults,
		});
	};

	const formatText = function (template) {
		const values = Array.prototype.slice.call(arguments, 1);
		let nextIndex = 0;

		return String(template || '').replace(/%((\d+)\$)?d/g, function (match, ignored, position) {
			const index = position ? parseInt(position, 10) - 1 : nextIndex++;
			return typeof values[index] !== 'undefined' ? values[index] : match;
		});
	};

	const clamp = function (value, min, max) {
		return Math.min(Math.max(value, min), max);
	};

		const thaiEncodedGlyphMap = {
			'\u0000': '',
			'\u000e': 'ช',
			'\u000f': 'ซ',
			'\u001e': 'บ',
			'\u001f': 'ป',
			N: 'เ',
			O: 'แ',
		};

		const normalizeSearchText = function (value) {
			return String(value || '')
				.toLocaleLowerCase()
				.replace(/[\u0e48-\u0e4c]/g, '')
				.replace(/\s+/g, ' ')
				.trim();
		};

		const compactSearchText = function (value) {
			return normalizeSearchText(value).replace(/\s+/g, '');
		};

		const normalizeExtractedSearchCharacter = function (character, sourceText) {
			if (!/[\u0000-\u001f\u0e00-\u0e7f]/.test(sourceText)) {
				return character;
			}

			if (Object.prototype.hasOwnProperty.call(thaiEncodedGlyphMap, character)) {
				return thaiEncodedGlyphMap[character];
			}

			if (/[\u0e48-\u0e4c]/.test(character)) {
				return '';
			}

			return character;
		};

		const hasTrailingWhitespace = function (value) {
			return /\s$/.test(String(value || ''));
		};

		const hasLeadingWhitespace = function (value) {
			return /^\s/.test(String(value || ''));
		};

		const shouldInsertTextSeparator = function (previousItem, item) {
			if (!previousItem) {
				return false;
			}

			if (previousItem.hasEOL || hasTrailingWhitespace(previousItem.str) || hasLeadingWhitespace(item.str)) {
				return true;
			}

			const previousTransform = previousItem.transform || [];
			const itemTransform = item.transform || [];
			const previousX = previousTransform[4];
			const previousY = previousTransform[5];
			const itemX = itemTransform[4];
			const itemY = itemTransform[5];

			if (![previousX, previousY, itemX, itemY].every(Number.isFinite)) {
				return false;
			}

			const previousHeight = Math.max(Math.abs(previousItem.height || 0), Math.abs(previousTransform[3] || 0), 1);
			if (Math.abs(itemY - previousY) > previousHeight * 0.5) {
				return true;
			}

			const previousWidth = Number.isFinite(previousItem.width) ? previousItem.width : 0;
			const previousTextLength = Math.max(Array.from(String(previousItem.str || '')).length, 1);
			const averageCharacterWidth = previousWidth / previousTextLength;
			const visualGap = itemX - (previousX + previousWidth);

			return visualGap > Math.max(averageCharacterWidth * 0.55, 2);
		};

	const initViewer = async function (viewer) {
		const settings = getViewerSettings(viewer);
		const pdfUrl = viewer.getAttribute('data-pdf-url');
		const pagesRoot = viewer.querySelector('[data-pdf-pages]');
		const stage = viewer.querySelector('.download-pdf-viewer__stage');
		const status = viewer.querySelector('[data-pdf-status]');
		const prevButton = viewer.querySelector('[data-pdf-prev]');
		const nextButton = viewer.querySelector('[data-pdf-next]');
		const pageLabel = viewer.querySelector('[data-pdf-page-label]');
		const searchInput = viewer.querySelector('[data-pdf-search-input]');
		const searchPrevButton = viewer.querySelector('[data-pdf-search-prev]');
		const searchNextButton = viewer.querySelector('[data-pdf-search-next]');
		const searchStatus = viewer.querySelector('[data-pdf-search-status]');
		const zoomOutButton = viewer.querySelector('[data-pdf-zoom-out]');
		const zoomInButton = viewer.querySelector('[data-pdf-zoom-in]');
		const zoomResetButton = viewer.querySelector('[data-pdf-zoom-reset]');
		const zoomLabel = viewer.querySelector('[data-pdf-zoom-label]');
		const pageSlider = viewer.querySelector('[data-pdf-page-slider]');
		const sliderLabel = viewer.querySelector('[data-pdf-slider-label]');
		const chapterSelect = viewer.querySelector('[data-pdf-chapter-select]');
		const documentMeta = viewer.querySelector('[data-pdf-document-meta]');
		const documentMetaTemplate = viewer.getAttribute('data-pdf-document-meta-template') || '';
		const thumbnailList = viewer.querySelector('[data-pdf-thumbnails]');
		const thumbnailToggleButtons = viewer.querySelectorAll('[data-pdf-thumb-toggle]');
		const thumbnailOpenButton = viewer.querySelector('.download-pdf-viewer__thumb-open');

		if (!pdfUrl || !pagesRoot) {
			return;
		}

		let pagesViewport = pagesRoot.parentElement;

		if (!pagesViewport || !pagesViewport.classList.contains('download-pdf-viewer__page-viewport')) {
			pagesViewport = document.createElement('div');
			pagesViewport.className = 'download-pdf-viewer__page-viewport';
			pagesRoot.parentNode.insertBefore(pagesViewport, pagesRoot);
			pagesViewport.appendChild(pagesRoot);
		}

		pagesViewport.style.boxSizing = 'border-box';
		pagesViewport.style.width = '100%';
		pagesViewport.style.height = '100%';
		pagesViewport.style.minHeight = '100%';
		pagesViewport.style.overflow = 'auto';

		if (settings.workerSrc) {
			pdfjsLib.GlobalWorkerOptions.workerSrc = settings.workerSrc;
		}

		let pdfDocument = null;
		let pageFlip = null;
		const basePageWidth = 760;
		const basePageMinWidth = 280;
		const zoomMin = 0.75;
		const zoomMax = 3;
		const zoomStep = 0.25;
		let zoom = 1;
		let pageRatio = 1075 / basePageWidth;
		let pageBaseWidth = basePageWidth;
		let pageBaseHeight = Math.round(pageBaseWidth * pageRatio);
		const pageEntries = new Map();
		const renderedPages = new Set();
		const renderingPages = new Map();
		const searchIndex = new Map();
		const thumbnails = new Map();
		const textContentCache = new Map();
		let searchIndexPromise = null;
		let searchResults = [];
		let searchResultIndex = -1;
		let searchSequence = 0;
		let searchDebounceTimer = null;
			let sliderDebounceTimer = null;
			let resizeDebounceTimer = null;
			let currentSearchQuery = '';
			let currentSearchCompactQuery = '';
			let chapters = [];
			let activeMousePan = null;
			let activeTouchPan = null;
			let activePinch = null;
			let thumbnailViewportFrame = null;

		const setStatus = function (message) {
			if (status) {
				status.textContent = message;
			}
		};

		const setSearchStatus = function (message) {
			if (searchStatus) {
				searchStatus.textContent = message;
			}
		};

		const setSliderPage = function (pageNumber) {
			if (sliderLabel && pdfDocument) {
				sliderLabel.textContent = pageNumber + ' / ' + pdfDocument.numPages;
			}

			if (pageSlider && pdfDocument) {
				pageSlider.value = String(pageNumber);
			}
		};

		const setDocumentMeta = function () {
			if (!documentMeta || !pdfDocument || !documentMetaTemplate) {
				return;
			}

			documentMeta.textContent = formatText(documentMetaTemplate, pdfDocument.numPages);
		};

		const updateActiveThumbnail = function (pageNumber) {
			let activeThumb = null;

			thumbnails.forEach(function (thumb, thumbPageNumber) {
				const isActive = thumbPageNumber === pageNumber;

				thumb.button.classList.toggle('is-active', isActive);
				thumb.button.setAttribute('aria-current', isActive ? 'page' : 'false');

				if (isActive) {
					activeThumb = thumb.button;
				}
			});

			if (activeThumb && thumbnailList) {
				activeThumb.scrollIntoView({
					block: 'nearest',
					inline: 'nearest',
				});
			}
		};

		const updateThumbnailViewport = function () {
			if (!thumbnailList || !pagesRoot || !pageEntries.size) {
				return;
			}

			thumbnails.forEach(function (thumb) {
				thumb.button.classList.remove('has-viewport');

				if (thumb.viewport) {
					thumb.viewport.removeAttribute('style');
				}
			});

			if (zoom <= 1.01) {
				return;
			}

			const viewportRect = pagesViewport.getBoundingClientRect();

			pageEntries.forEach(function (entry, pageNumber) {
				const thumb = thumbnails.get(pageNumber);

				if (!thumb || !thumb.viewport || !entry.canvasWrap) {
					return;
				}

				const pageRect = entry.canvasWrap.getBoundingClientRect();
				const left = Math.max(viewportRect.left, pageRect.left);
				const top = Math.max(viewportRect.top, pageRect.top);
				const right = Math.min(viewportRect.right, pageRect.right);
				const bottom = Math.min(viewportRect.bottom, pageRect.bottom);
				const width = right - left;
				const height = bottom - top;

				if (pageRect.width <= 0 || pageRect.height <= 0 || width < 4 || height < 4) {
					return;
				}

				thumb.viewport.style.left = clamp(((left - pageRect.left) / pageRect.width) * 100, 0, 100) + '%';
				thumb.viewport.style.top = clamp(((top - pageRect.top) / pageRect.height) * 100, 0, 100) + '%';
				thumb.viewport.style.width = clamp((width / pageRect.width) * 100, 0, 100) + '%';
				thumb.viewport.style.height = clamp((height / pageRect.height) * 100, 0, 100) + '%';
				thumb.button.classList.add('has-viewport');
			});
		};

		const scheduleThumbnailViewportUpdate = function () {
			if (thumbnailViewportFrame) {
				return;
			}

			thumbnailViewportFrame = window.requestAnimationFrame(function () {
				thumbnailViewportFrame = null;
				updateThumbnailViewport();
			});
		};

		const updateCurrentChapter = function (pageNumber) {
			if (!chapterSelect || !chapters.length) {
				return;
			}

			let selectedValue = '';

			chapters.forEach(function (chapter, index) {
				if (chapter.pageNumber <= pageNumber) {
					selectedValue = String(index);
				}
			});

			chapterSelect.value = selectedValue;
		};

		const setCurrentPage = function (pageIndex) {
			if (!pdfDocument) {
				return;
			}

			const pageNumber = clamp(pageIndex + 1, 1, pdfDocument.numPages);
			const message = formatText(settings.pageStatus, pageNumber, pdfDocument.numPages);

			setStatus(message);

			if (pageLabel) {
				pageLabel.textContent = message;
			}

			if (prevButton) {
				prevButton.disabled = pageNumber <= 1;
			}

			if (nextButton) {
				nextButton.disabled = pageNumber >= pdfDocument.numPages;
			}

			setSliderPage(pageNumber);
			updateActiveThumbnail(pageNumber);
			updateCurrentChapter(pageNumber);
		};

		const getStageContentWidth = function () {
			const contentStage = stage || pagesViewport.parentElement;

			if (!contentStage) {
				return pagesViewport.getBoundingClientRect().width || (pageBaseWidth * 2);
			}

			const stageStyle = window.getComputedStyle(contentStage);
			const paddingX = (parseFloat(stageStyle.paddingLeft) || 0) + (parseFloat(stageStyle.paddingRight) || 0);

			return Math.max(contentStage.clientWidth - paddingX, basePageMinWidth);
		};

		const updateZoomControls = function () {
			const isReady = Boolean(pdfDocument && pageFlip);

			viewer.classList.toggle('is-zoomed', zoom > 1.01);

			if (zoomLabel) {
				zoomLabel.textContent = Math.round(zoom * 100) + '%';
			}

			if (zoomOutButton) {
				zoomOutButton.disabled = !isReady || zoom <= zoomMin;
			}

			if (zoomInButton) {
				zoomInButton.disabled = !isReady || zoom >= zoomMax;
			}

			if (zoomResetButton) {
				zoomResetButton.disabled = !isReady || 1 === zoom;
			}
		};

		const updatePageControls = function () {
			const isReady = Boolean(pdfDocument && pageFlip);

			if (pageSlider) {
				pageSlider.disabled = !isReady;

				if (pdfDocument) {
					pageSlider.max = String(pdfDocument.numPages);
				}
			}
		};

		const applyBookLayout = function () {
			const minWidth = Math.round(basePageMinWidth * zoom);
			const minHeight = Math.round(minWidth * pageRatio);
			const desiredBookWidth = Math.max(minWidth, Math.round(getStageContentWidth() * zoom));

			pagesRoot.style.width = 1 === zoom ? '100%' : desiredBookWidth + 'px';
			pagesRoot.style.maxWidth = Math.max(desiredBookWidth, 2 * pageBaseWidth) + 'px';
			pagesRoot.style.minWidth = minWidth + 'px';
			pagesRoot.style.minHeight = minHeight + 'px';
		};

		const invalidateRenderedPages = function () {
			renderedPages.clear();

			pageEntries.forEach(function (entry) {
				entry.shell.classList.remove('is-rendered');
				entry.textItems = [];

				if (entry.highlightLayer) {
					entry.highlightLayer.innerHTML = '';
				}
			});
		};

		const applyZoomToFlipbook = function () {
			const currentPageIndex = pageFlip ? pageFlip.getCurrentPageIndex() : 0;
			const minWidth = Math.round(basePageMinWidth * zoom);

			pageBaseWidth = Math.round(basePageWidth * zoom);
			pageBaseHeight = Math.round(pageBaseWidth * pageRatio);
			applyBookLayout();

			if (!pageFlip) {
				return;
			}

			const settings = pageFlip.getSettings();

			settings.width = pageBaseWidth;
			settings.height = pageBaseHeight;
			settings.minWidth = minWidth;
			settings.maxWidth = pageBaseWidth;
			settings.minHeight = Math.round(minWidth * pageRatio);
			settings.maxHeight = pageBaseHeight;

			if (pageFlip.getUI && pageFlip.getUI().setOrientationStyle) {
				pageFlip.getUI().setOrientationStyle(pageFlip.getOrientation());
			} else {
				pageFlip.update();
			}

			pageFlip.turnToPage(currentPageIndex);
		};

		const setZoom = function (nextZoom) {
			const nextZoomValue = clamp(Math.round(nextZoom * 100) / 100, zoomMin, zoomMax);

			if (nextZoomValue === zoom) {
				return false;
			}

			zoom = nextZoomValue;
			applyZoomToFlipbook();
			invalidateRenderedPages();
			markSearchResults();
			updateZoomControls();
			scheduleThumbnailViewportUpdate();

			if (pageFlip) {
				setCurrentPage(pageFlip.getCurrentPageIndex());
				renderAround(pageFlip.getCurrentPageIndex());
			}

			return true;
		};

		const zoomAtPoint = function (nextZoom, clientX, clientY) {
			const previousZoom = zoom;
			const rect = pagesViewport.getBoundingClientRect();
			const localX = Number.isFinite(clientX) ? clamp(clientX - rect.left, 0, rect.width) : pagesViewport.clientWidth / 2;
			const localY = Number.isFinite(clientY) ? clamp(clientY - rect.top, 0, rect.height) : pagesViewport.clientHeight / 2;
			const scrollX = pagesViewport.scrollLeft + localX;
			const scrollY = pagesViewport.scrollTop + localY;

			if (!setZoom(nextZoom)) {
				return;
			}

			window.requestAnimationFrame(function () {
				const ratio = zoom / previousZoom;

				pagesViewport.scrollLeft = Math.max(0, (scrollX * ratio) - localX);
				pagesViewport.scrollTop = Math.max(0, (scrollY * ratio) - localY);
				updateThumbnailViewport();
			});
		};

		const isInteractiveTarget = function (target) {
			return Boolean(target && target.closest && target.closest('button, a, input, select, textarea, .download-pdf-viewer__toolbar, .download-pdf-viewer__controls, .download-pdf-viewer__searchbar, .download-pdf-viewer__thumb-rail'));
		};

		const getTouchDistance = function (touches) {
			if (!touches || touches.length < 2) {
				return 0;
			}

			const x = touches[0].clientX - touches[1].clientX;
			const y = touches[0].clientY - touches[1].clientY;

			return Math.sqrt((x * x) + (y * y));
		};

		const getTouchCenter = function (touches) {
			if (!touches || !touches.length) {
				return {
					x: pagesViewport.clientWidth / 2,
					y: pagesViewport.clientHeight / 2,
				};
			}

			if (touches.length < 2) {
				return {
					x: touches[0].clientX,
					y: touches[0].clientY,
				};
			}

			return {
				x: (touches[0].clientX + touches[1].clientX) / 2,
				y: (touches[0].clientY + touches[1].clientY) / 2,
			};
		};

		const updateSearchButtons = function (isBusy) {
			const hasResults = searchResults.length > 0;

			if (searchPrevButton) {
				searchPrevButton.disabled = isBusy || !hasResults;
			}

			if (searchNextButton) {
				searchNextButton.disabled = isBusy || !hasResults;
			}
		};

		const clearSearchMarks = function () {
			pageEntries.forEach(function (entry) {
				entry.shell.classList.remove('has-search-match', 'is-current-search-match');

				if (entry.highlightLayer) {
					entry.highlightLayer.innerHTML = '';
				}
			});
		};

		const createSearchTextModel = function (textContent) {
			const normalizedChars = [];
			const chars = [];
			const compactChars = [];
			const compactCharacters = [];
			let previousItem = null;

			const appendChar = function (character, reference) {
				if ('' === character) {
					return;
				}

				if (' ' === character && (!normalizedChars.length || ' ' === normalizedChars[normalizedChars.length - 1])) {
					return;
				}

				normalizedChars.push(character);
				chars.push(reference);

				if (' ' !== character) {
					compactCharacters.push(character);
					compactChars.push(reference);
				}
			};

			textContent.items.forEach(function (item, itemIndex) {
				const sourceText = String(item.str || '');

				if (!sourceText) {
					return;
				}

				if (shouldInsertTextSeparator(previousItem, item)) {
					appendChar(' ', { itemIndex: null, offset: 0 });
				}

				Array.from(sourceText).forEach(function (character, offset) {
					const extractedCharacter = normalizeExtractedSearchCharacter(character, sourceText);
					const normalizedCharacter = /\s/.test(extractedCharacter) ? ' ' : extractedCharacter.toLocaleLowerCase();
					appendChar(normalizedCharacter, {
						itemIndex: itemIndex,
						offset: offset,
					});
				});

				previousItem = item;
			});

			while (normalizedChars.length && ' ' === normalizedChars[normalizedChars.length - 1]) {
				normalizedChars.pop();
				chars.pop();
			}

			return {
				chars: chars,
				compactChars: compactChars,
				compactText: compactCharacters.join(''),
				text: normalizedChars.join(''),
			};
		};

		const findSearchMatches = function (sourceText, query) {
			const matches = [];
			let searchFrom = 0;
			let matchIndex = sourceText.indexOf(query, searchFrom);

			while (-1 !== matchIndex) {
				matches.push({
					end: matchIndex + query.length,
					start: matchIndex,
				});
				searchFrom = matchIndex + Math.max(query.length, 1);
				matchIndex = sourceText.indexOf(query, searchFrom);
			}

			return matches;
		};

		const createTextItemGeometry = function (item, viewport) {
			if (!item.str) {
				return null;
			}

			const transform = pdfjsLib.Util.transform(viewport.transform, item.transform);
			const rawWidth = Number.isFinite(item.width) ? Math.abs(item.width * viewport.scale) : 0;
			const rawHeight = Number.isFinite(item.height) ? Math.abs(item.height * viewport.scale) : 0;
			const itemWidth = Math.max(rawWidth, 1);
			const itemHeight = Math.max(Math.hypot(transform[2], transform[3]), rawHeight, 8);
			const left = transform[4];
			const top = transform[5] - itemHeight;

			return {
				heightPct: 100 * itemHeight / viewport.height,
				leftPct: 100 * left / viewport.width,
				text: item.str,
				textLength: Array.from(item.str).length,
				topPct: 100 * top / viewport.height,
				widthPct: 100 * itemWidth / viewport.width,
			};
		};

		const appendSearchHighlight = function (entry, item, startOffset, endOffset, isCurrentResult) {
			const textLength = Math.max(item.textLength || 0, 1);
			const charWidthPct = item.widthPct / textLength;
			const leftPct = item.leftPct + (charWidthPct * startOffset);
			const widthPct = Math.max(charWidthPct * Math.max(endOffset - startOffset, 1), 0.35);
			const safeLeftPct = clamp(leftPct, 0, 100);
			const marker = document.createElement('span');

			marker.className = 'download-pdf-viewer__search-highlight';

			if (isCurrentResult) {
				marker.classList.add('is-current');
			}

			marker.style.left = safeLeftPct + '%';
			marker.style.top = clamp(item.topPct, 0, 100) + '%';
			marker.style.width = clamp(widthPct, 0.35, 100 - safeLeftPct) + '%';
			marker.style.height = clamp(item.heightPct * 1.1, 0.8, 6) + '%';

			entry.highlightLayer.appendChild(marker);
		};

		const appendSearchHighlightsForMatch = function (entry, chars, match, isCurrentResult) {
			let activeSegment = null;

			const flushSegment = function () {
				if (!activeSegment) {
					return;
				}

				const item = entry.textItems[activeSegment.itemIndex];

				if (item) {
					appendSearchHighlight(entry, item, activeSegment.startOffset, activeSegment.endOffset, isCurrentResult);
				}

				activeSegment = null;
			};

			for (let index = match.start; index < match.end; index++) {
				const character = chars[index];

				if (!character || null === character.itemIndex) {
					flushSegment();
					continue;
				}

				if (!activeSegment || activeSegment.itemIndex !== character.itemIndex) {
					flushSegment();
					activeSegment = {
						endOffset: character.offset + 1,
						itemIndex: character.itemIndex,
						startOffset: character.offset,
					};
					continue;
				}

				activeSegment.endOffset = Math.max(activeSegment.endOffset, character.offset + 1);
			}

			flushSegment();
		};

		function applySearchHighlightsToPage(pageNumber) {
			const entry = pageEntries.get(pageNumber);

			if (!entry || !entry.highlightLayer) {
				return;
			}

			entry.highlightLayer.innerHTML = '';

			if (!currentSearchQuery || !entry.textItems || !entry.textItems.length) {
				return;
			}

			const isCurrentResult = searchResults[searchResultIndex] === pageNumber;
			const textData = textContentCache.get(pageNumber);

			if (!textData || !textData.normalizedText || !textData.chars) {
				return;
			}

			let matches = findSearchMatches(textData.normalizedText, currentSearchQuery);
			let matchChars = textData.chars;

			if (!matches.length && currentSearchCompactQuery && textData.compactText) {
				matches = findSearchMatches(textData.compactText, currentSearchCompactQuery);
				matchChars = textData.compactChars;
			}

			matches.forEach(function (match) {
				appendSearchHighlightsForMatch(entry, matchChars, match, isCurrentResult);
			});
		}

		const markSearchResults = function () {
			clearSearchMarks();

				searchResults.forEach(function (pageNumber) {
					const entry = pageEntries.get(pageNumber);

					if (!entry) {
						return;
					}

					if (renderedPages.has(pageNumber)) {
						applySearchHighlightsToPage(pageNumber);
					}
				});
		};

		const goToPageIndex = function (pageIndex) {
			if (!pageFlip) {
				return;
			}

			pageFlip.turnToPage(pageIndex);
			setCurrentPage(pageFlip.getCurrentPageIndex());
			renderAround(pageFlip.getCurrentPageIndex());
		};

		const goToSearchResult = function (resultIndex) {
			if (!searchResults.length) {
				return;
			}

			searchResultIndex = (resultIndex + searchResults.length) % searchResults.length;

			const pageNumber = searchResults[searchResultIndex];

			setSearchStatus(formatText(settings.searchResults, searchResultIndex + 1, searchResults.length));
			markSearchResults();
			renderPage(pageNumber).then(function () {
				applySearchHighlightsToPage(pageNumber);
			});
			goToPageIndex(pageNumber - 1);
		};

		const getPageTextData = async function (pageNumber) {
			if (textContentCache.has(pageNumber)) {
				return textContentCache.get(pageNumber);
			}

			const page = await pdfDocument.getPage(pageNumber);
			const textContent = await page.getTextContent();
			const searchTextModel = createSearchTextModel(textContent);
			const textData = {
				chars: searchTextModel.chars,
				compactChars: searchTextModel.compactChars,
				compactText: searchTextModel.compactText,
				normalizedText: searchTextModel.text,
				textContent: textContent,
			};

			textContentCache.set(pageNumber, textData);

			return textData;
		};

		const resolveDestinationPageNumber = async function (destination) {
			let explicitDestination = destination;

			if (!pdfDocument || !explicitDestination) {
				return null;
			}

			if ('string' === typeof explicitDestination) {
				explicitDestination = await pdfDocument.getDestination(explicitDestination);
			}

			if (!Array.isArray(explicitDestination) || !explicitDestination.length) {
				return null;
			}

			const pageReference = explicitDestination[0];
			let pageIndex = null;

			if ('number' === typeof pageReference) {
				pageIndex = pageReference;
			} else if (pageReference) {
				pageIndex = await pdfDocument.getPageIndex(pageReference);
			}

			if (!Number.isFinite(pageIndex)) {
				return null;
			}

			return clamp(pageIndex + 1, 1, pdfDocument.numPages);
		};

		const collectOutlineChapters = async function (items, depth, list) {
			for (const item of items) {
				const pageNumber = await resolveDestinationPageNumber(item.dest);

				if (pageNumber) {
					list.push({
						depth: depth,
						pageNumber: pageNumber,
						title: item.title || formatText(settings.pageLabel, pageNumber, pdfDocument.numPages),
					});
				}

				if (item.items && item.items.length) {
					await collectOutlineChapters(item.items, depth + 1, list);
				}
			}

			return list;
		};

		const populateChapterSelect = async function () {
			if (!chapterSelect || !pdfDocument) {
				return;
			}

			const placeholder = chapterSelect.getAttribute('data-pdf-chapter-placeholder') || 'Chapters';
			const emptyLabel = chapterSelect.getAttribute('data-pdf-chapter-empty') || 'No chapters';

			chapterSelect.innerHTML = '';
			chapterSelect.appendChild(new Option(placeholder, ''));
			chapterSelect.disabled = true;

			try {
				const outline = await pdfDocument.getOutline();

				if (!outline || !outline.length) {
					chapterSelect.options[0].textContent = emptyLabel;
					return;
				}

				chapters = await collectOutlineChapters(outline, 0, []);

				if (!chapters.length) {
					chapterSelect.options[0].textContent = emptyLabel;
					return;
				}

				chapters.forEach(function (chapter, index) {
					const indent = chapter.depth ? Array(chapter.depth + 1).join(' - ') : '';
					const option = new Option(indent + chapter.title, String(index));

					chapterSelect.appendChild(option);
				});

				chapterSelect.disabled = false;
				updateCurrentChapter(pageFlip ? pageFlip.getCurrentPageIndex() + 1 : 1);
			} catch (error) {
				window.console.warn('Thaiadapp PDF outline unavailable:', error);
				chapterSelect.options[0].textContent = emptyLabel;
			}
		};

		const renderPage = function (pageNumber) {
			if (!pdfDocument || renderedPages.has(pageNumber)) {
				return Promise.resolve();
			}

			if (renderingPages.has(pageNumber)) {
				return renderingPages.get(pageNumber);
			}

			const entry = pageEntries.get(pageNumber);

			if (!entry) {
				return Promise.resolve();
			}

			const renderPromise = (async function () {
				entry.shell.classList.add('is-rendering');
				entry.caption.textContent = formatText(settings.loadingPage, pageNumber);

				try {
					const page = await pdfDocument.getPage(pageNumber);
					const baseViewport = page.getViewport({ scale: 1 });
					const viewport = page.getViewport({ scale: pageBaseWidth / baseViewport.width });
					const pixelRatio = clamp(window.devicePixelRatio || 1, 1, 1.75);
					const canvas = entry.canvas;
					const context = canvas.getContext('2d', { alpha: false });
					const textData = await getPageTextData(pageNumber);

					canvas.width = Math.floor(viewport.width * pixelRatio);
					canvas.height = Math.floor(viewport.height * pixelRatio);
					canvas.style.width = '100%';
					canvas.style.height = '100%';

					await page.render({
						canvasContext: context,
						viewport: viewport,
						transform: pixelRatio !== 1 ? [pixelRatio, 0, 0, pixelRatio, 0, 0] : null,
					}).promise;

					renderedPages.add(pageNumber);
					entry.textItems = textData.textContent.items.map(function (item) {
						return createTextItemGeometry(item, viewport);
					});
					entry.shell.classList.add('is-rendered');
					entry.caption.textContent = formatText(settings.pageLabel, pageNumber, pdfDocument.numPages);
					applySearchHighlightsToPage(pageNumber);
				} catch (error) {
					window.console.error('Thaiadapp PDF flipbook error:', error);
					entry.caption.textContent = settings.viewerError;
					setStatus(settings.viewerError);
					viewer.classList.add('has-error');
				} finally {
					entry.shell.classList.remove('is-rendering');
					renderingPages.delete(pageNumber);
				}
			}());

			renderingPages.set(pageNumber, renderPromise);

			return renderPromise;
		};

		const indexPageText = async function (pageNumber) {
			if (searchIndex.has(pageNumber)) {
				return searchIndex.get(pageNumber);
			}

			const textData = await getPageTextData(pageNumber);
			const normalizedText = {
				compactText: textData.compactText,
				normalizedText: textData.normalizedText,
			};

			searchIndex.set(pageNumber, normalizedText);

			return normalizedText;
		};

		const buildSearchIndex = function () {
			if (searchIndexPromise) {
				return searchIndexPromise;
			}

			searchIndexPromise = (async function () {
				for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
					setSearchStatus(formatText(settings.searchIndexing, pageNumber, pdfDocument.numPages));
					await indexPageText(pageNumber);
				}

				return searchIndex;
			}());

			return searchIndexPromise;
		};

		const runSearch = async function (query) {
			const normalizedQuery = normalizeSearchText(query);
			const currentSequence = ++searchSequence;

				if (!normalizedQuery) {
					currentSearchQuery = '';
					currentSearchCompactQuery = '';
					searchResults = [];
				searchResultIndex = -1;
				clearSearchMarks();
				updateSearchButtons(false);
				setSearchStatus(settings.searchReady);
				return;
			}

				try {
					currentSearchQuery = normalizedQuery;
					currentSearchCompactQuery = compactSearchText(query);
					updateSearchButtons(true);
				await buildSearchIndex();

				if (currentSequence !== searchSequence) {
					return;
				}

				searchResults = [];

				searchIndex.forEach(function (pageText, pageNumber) {
					if (
						pageText.normalizedText.includes(normalizedQuery) ||
						(currentSearchCompactQuery && pageText.compactText.includes(currentSearchCompactQuery))
					) {
						searchResults.push(pageNumber);
					}
				});

				searchResults.sort(function (a, b) {
					return a - b;
				});

				searchResultIndex = -1;

				if (!searchResults.length) {
					currentSearchQuery = '';
					currentSearchCompactQuery = '';
					clearSearchMarks();
					updateSearchButtons(false);
					setSearchStatus(settings.searchNoResults);
					return;
				}

				updateSearchButtons(false);
				goToSearchResult(0);
			} catch (error) {
				window.console.error('Thaiadapp PDF search error:', error);
				searchResults = [];
				searchResultIndex = -1;
				clearSearchMarks();
				updateSearchButtons(false);
				setSearchStatus(settings.viewerError);
			}
		};

		const renderAround = function (pageIndex) {
			if (!pdfDocument) {
				return;
			}

			[
				pageIndex,
				pageIndex + 1,
				pageIndex + 2,
				pageIndex - 1,
			].forEach(function (index) {
				if (index >= 0 && index < pdfDocument.numPages) {
					renderPage(index + 1);
				}
			});
		};

		const createPageShells = function () {
			const fragment = document.createDocumentFragment();

			pagesRoot.innerHTML = '';
			pageEntries.clear();

			for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
				const shell = document.createElement('div');
				const canvasWrap = document.createElement('div');
				const canvas = document.createElement('canvas');
				const highlightLayer = document.createElement('div');
				const caption = document.createElement('span');

				shell.className = 'download-pdf-viewer__page';
				shell.setAttribute('data-pdf-page', String(pageNumber));

				if (1 === pageNumber || pageNumber === pdfDocument.numPages) {
					shell.setAttribute('data-density', 'hard');
				}

				canvasWrap.className = 'download-pdf-viewer__canvas-wrap';
				highlightLayer.className = 'download-pdf-viewer__search-highlight-layer';
				canvas.setAttribute('aria-label', formatText(settings.pageLabel, pageNumber, pdfDocument.numPages));
				caption.className = 'download-pdf-viewer__page-label';
				caption.textContent = formatText(settings.loadingPage, pageNumber);

				canvasWrap.appendChild(canvas);
				canvasWrap.appendChild(highlightLayer);
				shell.appendChild(canvasWrap);
				shell.appendChild(caption);
				fragment.appendChild(shell);

				pageEntries.set(pageNumber, {
					canvas: canvas,
					canvasWrap: canvasWrap,
					caption: caption,
					highlightLayer: highlightLayer,
					shell: shell,
					textItems: [],
				});
			}

			pagesRoot.appendChild(fragment);
		};

		const renderThumbnail = async function (pageNumber, canvas) {
			try {
				const page = await pdfDocument.getPage(pageNumber);
				const baseViewport = page.getViewport({ scale: 1 });
				const viewport = page.getViewport({ scale: 114 / baseViewport.width });
				const pixelRatio = clamp(window.devicePixelRatio || 1, 1, 1.5);
				const context = canvas.getContext('2d', { alpha: false });

				canvas.width = Math.floor(viewport.width * pixelRatio);
				canvas.height = Math.floor(viewport.height * pixelRatio);
				canvas.style.width = '100%';
				canvas.style.height = '100%';

				await page.render({
					canvasContext: context,
					viewport: viewport,
					transform: pixelRatio !== 1 ? [pixelRatio, 0, 0, pixelRatio, 0, 0] : null,
				}).promise;
			} catch (error) {
				window.console.warn('LearningForKidz PDF thumbnail unavailable:', error);
			}
		};

		const createThumbnails = function () {
			if (!thumbnailList || !pdfDocument) {
				return;
			}

			const fragment = document.createDocumentFragment();

			thumbnailList.innerHTML = '';
			thumbnails.clear();

			for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
				const button = document.createElement('button');
				const canvas = document.createElement('canvas');
				const viewport = document.createElement('span');
				const number = document.createElement('span');

				button.type = 'button';
				button.className = 'download-pdf-viewer__thumb';
				button.setAttribute('aria-label', formatText(settings.pageLabel, pageNumber, pdfDocument.numPages));
				button.addEventListener('click', function () {
					goToPageIndex(pageNumber - 1);
				});

				number.className = 'download-pdf-viewer__thumb-number';
				viewport.className = 'download-pdf-viewer__thumb-viewport';
				number.textContent = String(pageNumber);

				button.appendChild(canvas);
				button.appendChild(viewport);
				button.appendChild(number);
				fragment.appendChild(button);

				thumbnails.set(pageNumber, {
					button: button,
					canvas: canvas,
					viewport: viewport,
				});
			}

			thumbnailList.appendChild(fragment);

			(async function () {
				for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
					const thumb = thumbnails.get(pageNumber);

					if (thumb) {
						await renderThumbnail(pageNumber, thumb.canvas);
					}
				}
			}());
		};

		const initFlipbook = function () {
			const pageItems = Array.from(pageEntries.values()).map(function (entry) {
				return entry.shell;
			});
			const minWidth = Math.round(basePageMinWidth * zoom);
			const maxWidth = pageBaseWidth;
			const minHeight = Math.round(minWidth * (pageBaseHeight / pageBaseWidth));

			applyBookLayout();

			pageFlip = new PageFlip(pagesRoot, {
				width: pageBaseWidth,
				height: pageBaseHeight,
				size: 'stretch',
				minWidth: minWidth,
				maxWidth: maxWidth,
				minHeight: minHeight,
				maxHeight: pageBaseHeight,
				drawShadow: true,
				flippingTime: 700,
				usePortrait: true,
				startZIndex: 1,
				autoSize: true,
				maxShadowOpacity: 0.35,
				showCover: true,
				mobileScrollSupport: true,
				swipeDistance: 30,
				clickEventForward: true,
				disableFlipByClick: true,
				showPageCorners: false,
			});

			pageFlip.loadFromHTML(pageItems);
			applyBookLayout();

			pageFlip.on('flip', function (event) {
				setCurrentPage(event.data);
				renderAround(event.data);
				scheduleThumbnailViewportUpdate();
			});

			pageFlip.on('changeOrientation', function () {
				renderAround(pageFlip.getCurrentPageIndex());
				scheduleThumbnailViewportUpdate();
			});

			setCurrentPage(pageFlip.getCurrentPageIndex());
			renderAround(pageFlip.getCurrentPageIndex());
			viewer.classList.add('is-ready');
			scheduleThumbnailViewportUpdate();
		};

		if (prevButton) {
			prevButton.addEventListener('click', function () {
				if (pageFlip) {
					pageFlip.flipPrev('bottom');
				}
			});
		}

		if (nextButton) {
			nextButton.addEventListener('click', function () {
				if (pageFlip) {
					pageFlip.flipNext('bottom');
				}
			});
		}

		if (zoomOutButton) {
			zoomOutButton.addEventListener('click', function () {
				zoomAtPoint(zoom - zoomStep);
			});
		}

		if (zoomInButton) {
			zoomInButton.addEventListener('click', function () {
				zoomAtPoint(zoom + zoomStep);
			});
		}

		if (zoomResetButton) {
			zoomResetButton.addEventListener('click', function () {
				setZoom(1);
			});
		}

		if (stage) {
			stage.addEventListener('wheel', function (event) {
				if (isInteractiveTarget(event.target)) {
					return;
				}

				if (event.ctrlKey || event.metaKey) {
					event.preventDefault();
					zoomAtPoint(zoom + (event.deltaY > 0 ? -0.12 : 0.12), event.clientX, event.clientY);
					return;
				}

				if (zoom <= 1.01) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
				pagesViewport.scrollLeft += event.deltaX || (event.shiftKey ? event.deltaY : 0);
				pagesViewport.scrollTop += event.shiftKey ? 0 : event.deltaY;
				scheduleThumbnailViewportUpdate();
			}, { passive: false });

			pagesViewport.addEventListener('pointerdown', function (event) {
				if ('touch' === event.pointerType || event.button || zoom <= 1.01 || isInteractiveTarget(event.target)) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
				activeMousePan = {
					pointerId: event.pointerId,
					x: event.clientX,
					y: event.clientY,
					left: pagesViewport.scrollLeft,
					top: pagesViewport.scrollTop,
				};

				if (pagesViewport.setPointerCapture) {
					pagesViewport.setPointerCapture(event.pointerId);
				}

				viewer.classList.add('is-panning');
			}, true);

			pagesViewport.addEventListener('pointermove', function (event) {
				if (!activeMousePan || event.pointerId !== activeMousePan.pointerId) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
				pagesViewport.scrollLeft = activeMousePan.left - (event.clientX - activeMousePan.x);
				pagesViewport.scrollTop = activeMousePan.top - (event.clientY - activeMousePan.y);
				scheduleThumbnailViewportUpdate();
			}, true);

			const stopPointerPan = function (event) {
				if (!activeMousePan || event.pointerId !== activeMousePan.pointerId) {
					return;
				}

				if (pagesViewport.releasePointerCapture && (!pagesViewport.hasPointerCapture || pagesViewport.hasPointerCapture(event.pointerId))) {
					pagesViewport.releasePointerCapture(event.pointerId);
				}

				activeMousePan = null;
				viewer.classList.remove('is-panning');
				scheduleThumbnailViewportUpdate();
			};

			pagesViewport.addEventListener('pointerup', stopPointerPan, true);
			pagesViewport.addEventListener('pointercancel', stopPointerPan, true);

			stage.addEventListener('click', function (event) {
				if (zoom <= 1.01 || isInteractiveTarget(event.target)) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
			}, true);

			stage.addEventListener('touchstart', function (event) {
				if (isInteractiveTarget(event.target)) {
					return;
				}

				if (event.touches.length >= 2) {
					activePinch = {
						distance: getTouchDistance(event.touches),
						zoom: zoom,
					};
					activeTouchPan = null;
					event.preventDefault();
					event.stopPropagation();
					return;
				}

				if (zoom > 1.01 && event.touches.length) {
					activeTouchPan = {
						x: event.touches[0].clientX,
						y: event.touches[0].clientY,
						left: pagesViewport.scrollLeft,
						top: pagesViewport.scrollTop,
					};
					viewer.classList.add('is-panning');
					event.stopPropagation();
				}
			}, { capture: true, passive: false });

			stage.addEventListener('touchmove', function (event) {
				if (isInteractiveTarget(event.target)) {
					return;
				}

				if (activePinch && event.touches.length >= 2) {
					const distance = getTouchDistance(event.touches);
					const center = getTouchCenter(event.touches);

					event.preventDefault();
					event.stopPropagation();

					if (activePinch.distance > 0) {
						zoomAtPoint(activePinch.zoom * (distance / activePinch.distance), center.x, center.y);
					}

					return;
				}

				if (activeTouchPan && zoom > 1.01 && event.touches.length) {
					event.preventDefault();
					event.stopPropagation();
					pagesViewport.scrollLeft = activeTouchPan.left - (event.touches[0].clientX - activeTouchPan.x);
					pagesViewport.scrollTop = activeTouchPan.top - (event.touches[0].clientY - activeTouchPan.y);
					scheduleThumbnailViewportUpdate();
				}
			}, { capture: true, passive: false });

			['touchend', 'touchcancel'].forEach(function (eventName) {
				stage.addEventListener(eventName, function (event) {
					if (event.touches.length < 2) {
						activePinch = null;
					}

					if (!event.touches.length) {
						activeTouchPan = null;
						viewer.classList.remove('is-panning');
					}

					if (zoom > 1.01 && !isInteractiveTarget(event.target)) {
						event.stopPropagation();
					}
				}, { capture: true, passive: false });
			});
		}

		pagesViewport.addEventListener('scroll', scheduleThumbnailViewportUpdate, { passive: true });

		if (pageSlider) {
			pageSlider.disabled = true;
			pageSlider.addEventListener('input', function () {
				if (!pdfDocument) {
					return;
				}

				const pageNumber = clamp(parseInt(pageSlider.value, 10) || 1, 1, pdfDocument.numPages);

				setSliderPage(pageNumber);
				window.clearTimeout(sliderDebounceTimer);
				sliderDebounceTimer = window.setTimeout(function () {
					goToPageIndex(pageNumber - 1);
				}, 120);
			});
			pageSlider.addEventListener('change', function () {
				if (!pdfDocument) {
					return;
				}

				const pageNumber = clamp(parseInt(pageSlider.value, 10) || 1, 1, pdfDocument.numPages);

				window.clearTimeout(sliderDebounceTimer);
				goToPageIndex(pageNumber - 1);
			});
		}

		if (chapterSelect) {
			chapterSelect.disabled = true;
			chapterSelect.addEventListener('change', function () {
				const selectedIndex = parseInt(chapterSelect.value, 10);
				const chapter = chapters[selectedIndex];

				if (!chapter) {
					return;
				}

				goToPageIndex(chapter.pageNumber - 1);
			});
		}

		window.addEventListener('resize', function () {
			window.clearTimeout(resizeDebounceTimer);
			resizeDebounceTimer = window.setTimeout(function () {
				if (!pageFlip) {
					return;
				}

				applyZoomToFlipbook();
				renderAround(pageFlip.getCurrentPageIndex());
				scheduleThumbnailViewportUpdate();
			}, 150);
		});

		if (searchInput) {
			searchInput.disabled = true;
			searchInput.addEventListener('input', function () {
				window.clearTimeout(searchDebounceTimer);
				searchDebounceTimer = window.setTimeout(function () {
					runSearch(searchInput.value);
				}, 350);
			});
			searchInput.addEventListener('keydown', function (event) {
				if ('Enter' === event.key) {
					event.preventDefault();
					window.clearTimeout(searchDebounceTimer);
					runSearch(searchInput.value);
				}
			});
			searchInput.addEventListener('search', function () {
				window.clearTimeout(searchDebounceTimer);
				runSearch(searchInput.value);
			});
		}

		if (searchPrevButton) {
			searchPrevButton.addEventListener('click', function () {
				goToSearchResult(searchResultIndex - 1);
			});
		}

		if (searchNextButton) {
			searchNextButton.addEventListener('click', function () {
				goToSearchResult(searchResultIndex + 1);
			});
		}

		thumbnailToggleButtons.forEach(function (button) {
			button.addEventListener('click', function () {
				const isCollapsed = viewer.classList.toggle('is-rail-collapsed');

				if (thumbnailOpenButton) {
					thumbnailOpenButton.hidden = !isCollapsed;
				}
			});
		});

		updateSearchButtons(true);
		updateZoomControls();
		updatePageControls();
		setSearchStatus(settings.searchReady);

		try {
			setStatus(settings.loading);

			const loadingTask = pdfjsLib.getDocument({ url: pdfUrl });
			pdfDocument = await loadingTask.promise;

			const firstPage = await pdfDocument.getPage(1);
			const firstViewport = firstPage.getViewport({ scale: 1 });

			pageRatio = firstViewport.height / firstViewport.width;
			pageBaseHeight = Math.round(pageBaseWidth * pageRatio);

			setDocumentMeta();
			createPageShells();
			createThumbnails();
			await renderPage(1);
			initFlipbook();
			populateChapterSelect();

			if (searchInput) {
				searchInput.disabled = false;
			}

			updateSearchButtons(false);
			updateZoomControls();
			updatePageControls();
			setSliderPage(pageFlip.getCurrentPageIndex() + 1);
			setSearchStatus(settings.searchReady);
		} catch (error) {
			window.console.error('LearningForKidz PDF flipbook error:', error);
			setStatus(settings.viewerError);
			viewer.classList.add('has-error');
		}
	};

	document.querySelectorAll('[data-lfk-pdf-viewer]').forEach(function (viewer) {
		initViewer(viewer);
	});
})();
