/**
 * Normalize a scanned QR / tag string into the canonical Firestore document ID.
 * Handles full URLs, query params, and stray whitespace.
 */
export function normalizeTagId(raw) {
    if (!raw || typeof raw !== 'string') return '';
    let tag = raw.trim();
    if (!tag) return '';

    // If the QR encodes a full URL, extract the id= query value
    if (tag.includes('id=')) {
        tag = tag.split('id=').pop().split('&')[0];
    }

    // Strip trailing slashes or path segments if a bare URL was scanned
    if (tag.includes('/')) {
        const parts = tag.split('/').filter(Boolean);
        const last = parts[parts.length - 1] || '';
        if (last && !last.includes('.')) {
            tag = last;
        }
    }

    try {
        tag = decodeURIComponent(tag);
    } catch (e) {
        // keep original if decode fails
    }

    return tag.trim();
}
