db.collection('tickets').where('isArchived', '==', false).onSnapshot((snapshot) => {
    snapshot.forEach((doc) => {
        let ticket = doc.data();
        
        // 1. Mask Email if not staff
        let displayEmail = ticket.reporterEmail;
        if (currentUserRole !== 'staff') {
            // Turns admin@tarumt.edu.my into ad***@tarumt.edu.my
            displayEmail = displayEmail.replace(/(.{2})(.*)(?=@)/, "$1***"); 
        }

        // 2. Format Date
        let reportDate = ticket.createdAt ? ticket.createdAt.toDate().toLocaleDateString() : 'Just now';

        // 3. Render HTML with Badges
        let statusBadge = "";
        if(ticket.status === "Pending") statusBadge = `<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Pending</span>`;
        if(ticket.status === "In Progress") statusBadge = `<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">In Progress</span>`;
        if(ticket.status === "Completed") statusBadge = `<span class="bg-green-100 text-green-700 px-2 py-1 rounded">Completed</span>`;

        // Render your ticket row here using displayEmail, reportDate, and statusBadge...
    });
});