/**
 * public/js/search.js
 */

const searchInput = document.getElementById('globalSearchInput');
const searchResults = document.getElementById('globalSearchResults');

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value;
        
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        // Call API
        fetch(`api.php?action=search&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success' && response.data.length > 0) {
                    let html = '<ul class="py-2 text-sm text-gray-700">';
                    
                    response.data.forEach(item => {
                        let link = '#';
                        let icon = 'fa-circle';
                        
                        // Determine Link based on type
                        if(item.type === 'farm') {
                            link = `views/farms/show.php?id=${item.id}`;
                            icon = 'fa-tractor text-emerald-500';
                        } else if (item.type === 'owner') {
                            link = `views/farms/index.php?q=${encodeURIComponent(item.title)}`;
                            icon = 'fa-user text-blue-500';
                        }

                        html += `
                            <li>
                                <a href="${link}" class="block px-4 py-2 hover:bg-gray-100 flex items-center">
                                    <i class="fas ${icon} mr-2"></i> ${item.title}
                                    <span class="text-xs text-gray-400 ml-auto capitalize">${item.type}</span>
                                </a>
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
                    searchResults.innerHTML = html;
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">No results found</div>';
                    searchResults.classList.remove('hidden');
                }
            });
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
}