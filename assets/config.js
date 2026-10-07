/* ===== VOTESS SITE SETTINGS =====
   Edit this one file to change menus, product website links, social links and news content.
   Every page reads from here. */
window.VOTESS = {
  email: 'info@votess.in',

  /* PRODUCT WEBSITES: replace each url with the real product website. The CTA opens it in a new tab. */
  products: [
    { id:'nexus', name:'Nexus ERP', sector:'Education', tag:'A VOTESS Product', url:'https://nexuserp.example.com', cta:'Visit Nexus ERP website', demo:'erp_demo',
      desc:'School management software that puts administration, teachers, students and parents on one system.',
      feat:['Administration & portals','Finance & HR'],
      allFeat:['Student, teacher and parent portals','Admissions and online fee collection','Attendance tracking with RFID / biometric','Gradebook, exam reports & report cards','Transport management with GPS tracking','Integrated LMS and online classrooms','Staff HR, payroll and leave management','Library, hostel and asset management','Comprehensive executive analytics'],
      fullDesc:'Nexus ERP is our flagship education management platform designed for modern schools, colleges, and university campuses. It unifies operations across administration, academic delivery, transport logistics, and parent communication into a single secure cloud architecture.',
      grad:'linear-gradient(135deg,#86BC25,#26890D)' },
    { id:'govacay', name:'GoVacayTrip', sector:'Travel', tag:'Powered by VOTESS', url:'https://govacaytrip.example.com', cta:'Visit GoVacayTrip website', demo:'travel',
      desc:'A travel brand for booking the whole trip in one place, without customers needing to know what sits underneath.',
      feat:['Flight booking','Hotels & resorts'],
      allFeat:['Domestic & international flight ticketing','Hotel, resort and holiday home bookings','Customized domestic and global tour packages','Inter-city bus ticketing with seat selection','Automated itinerary generation & travel alerts','Instant booking confirmation via SMS/Email','Integrated multi-currency payment options','24/7 customer support and rescheduling'],
      fullDesc:'GoVacayTrip simplifies the travel experience by bringing flights, accommodations, customized tour packages, and road transport into one intuitive interface. Built on high-speed GDS integrations and direct hospitality partnerships.',
      grad:'linear-gradient(135deg,#00A3E0,#0076A8)' },
    { id:'ciao', name:'Ciao Chat', sector:'Communication', tag:'A VOTESS Product / Service', url:'https://ciaochat.example.com', cta:'Visit Ciao Chat website', demo:'chat',
      desc:'Communication and customer-engagement tools that help organisations stay in touch with the people they serve.',
      feat:['Direct messaging','Customer engagement'],
      allFeat:['Omnichannel inbox (Web, WhatsApp, SMS)','Automated conversational AI bots & triage','Targeted broadcast messaging & updates','Customer ticket escalation and SLA tracking','Real-time agent queue & supervision tools','CRM contact synchronization','Enterprise security and audit logging','Response time and resolution analytics'],
      fullDesc:'Ciao Chat empowers businesses, public institutions, and teams to build genuine relationships with their customers through real-time messaging, automated inquiry resolution, and seamless multi-channel support.',
      grad:'linear-gradient(135deg,#53565A,#000)' },
    { id:'ngoerp', name:'Non-Profit ERP', sector:'Social Impact', tag:'Status: Under Development', url:'contact.html?purpose=ngoerp', cta:'Request Early Access', demo:'ngoerp',
      desc:'ERP and CRM platform empowering NGOs and non-profits to manage donors, projects and community impact.',
      feat:['Donor & CRM tools','Projects & volunteers'],
      allFeat:['Donor relationship management & history','Beneficiary registration and welfare tracking','Project deliverables and task management','Volunteer recruitment and shift scheduling','Grant application tracking & fund utilization','NGO accounting & compliance (80G/12A/FCRA)','Mobile field surveys with offline sync','Auditable social impact metrics & reporting'],
      fullDesc:'Non-Profit ERP is our dedicated A-to-Z operational platform engineered exclusively for non-profit organisations, charitable trusts, and community foundations. It streamlines donor management, project execution, volunteer rosters, and regulatory compliance.',
      grad:'linear-gradient(135deg,#1a237e,#0d47a1)' }
  ],

  /* SOCIAL LINKS: replace with your real pages. Leave '' to hide an icon. */
  social: {
    linkedin:'https://www.linkedin.com/company/your-page', x:'https://x.com/your-handle', facebook:'https://www.facebook.com/your-page',
    instagram:'https://www.instagram.com/your-handle', youtube:'https://www.youtube.com/@your-channel', whatsapp:'https://wa.me/910000000000'
  },

  /* MENU: every item is a page and opens in a new tab. */
  menu: [
    { label:'About us', href:'about.html', children:[
      { label:'About VOTESS', href:'about.html', d:'Our story, mission and values' },
      { label:'Who we are', href:'who-we-are.html', d:'How the group is structured' },
      { label:'What we do', href:'what-we-do.html', d:'Our capabilities and services' },
      { label:'Our team', href:'team.html', d:'The people behind VOTESS' },
      { label:'Careers', href:'careers.html', d:'Join the VOTESS team' },
      { label:'Partners & clients', href:'partners.html', d:'Work with us' } ] },
    { label:'What we do', href:'what-we-do.html', children:[
      { label:'Digital transformation', href:'what-we-do.html#digital', d:'Web, apps, cloud, automation' },
      { label:'Enterprise software', href:'what-we-do.html#software', d:'ERP and SaaS platforms' },
      { label:'Our products', href:'what-we-do.html#products', d:'Nexus ERP, GoVacayTrip, Ciao Chat, NGO ERP' } ] },
    { label:'Nayibeej', href:'nayibeej.html' },
    { label:'Our thinking', href:'our-thinking.html' },
    { label:'Newsroom', href:'newsroom.html', children:[
      { label:'Newsroom', href:'newsroom.html', d:'Company news and updates' },
      { label:'Events', href:'events.html', d:'Webinars, launches and meetups' },
      { label:'Press releases', href:'press-releases.html', d:'Official announcements' } ] },
    { label:'Contact', href:'contact.html' },
    { label:'Submit RFP', href:'submit-rfp.html', cta:true }
  ],

  /* CONTENT for the Our thinking, Newsroom, Events and Press pages. Replace the samples with real items. */
  thinking: [
    { tag:'Education technology', title:'One system for the whole school', text:'Why administrators, teachers and parents work better from a single platform.', date:'Oct 2026' },
    { tag:'Technology strategy', title:'Building once, reusing everywhere', text:'How a shared technology layer lets new brands launch faster and more safely.', date:'Oct 2026' },
    { tag:'Social impact', title:'Technology in service of non-profits', text:'Practical ways skills and tools can support social work without blurring accountability.', date:'Sep 2026' },
    { tag:'Travel', title:'Booking the whole trip in one place', text:'What customers expect from a modern travel experience.', date:'Sep 2026' },
    { tag:'Communication', title:'Staying close to the people you serve', text:'Messaging and engagement lessons for organisations of every size.', date:'Aug 2026' },
    { tag:'Technology strategy', title:'Lean first, then scale', text:'Why we validate a brand before giving it its own company.', date:'Aug 2026' }
  ],
  news: [
    { tag:'Product', title:'Non-Profit ERP enters active development for NGOs', text:'A complete management suite covering donor CRM, project tracking, grants and impact reporting.', date:'06 Oct 2026' },
    { tag:'Company', title:'VOTESS unveils its brand and group structure', text:'One technology backbone supporting education, travel and communication brands.', date:'03 Oct 2026' },
    { tag:'Technology', title:'VOTESS deploys zero-trust security framework', text:'Multi-factor authentication and AES-256 encrypted data pipelines rolled out across all portals.', date:'28 Sep 2026' },
    { tag:'Product', title:'Nexus ERP prepares for school launches', text:'The platform brings portals, finance, HR, transport and LMS together.', date:'25 Sep 2026' },
    { tag:'Partnership', title:'Ciao Chat announces omnichannel messaging integration', text:'Connecting web chat, WhatsApp and carrier SMS for customer engagement.', date:'18 Sep 2026' },
    { tag:'Responsibility', title:'Nayibeej: a non-profit for every social category', text:'An independent non-profit supported by VOTESS.', date:'12 Sep 2026' }
  ],
  events: [
    { tag:'Demo', title:'Non-Profit ERP early access showcase', text:'A live interactive preview for NGO leaders, charitable trusts and foundations.', date:'12 Nov 2026' },
    { tag:'Webinar', title:'Running a school on one platform', text:'A live walkthrough of Nexus ERP for school leaders, principals and trustees.', date:'18 Nov 2026' },
    { tag:'Launch', title:'GoVacayTrip consumer launch event', text:'Meet the engineering team and preview the unified trip booking experience.', date:'26 Nov 2026' },
    { tag:'Meetup', title:'Technology for social good and community impact', text:'An open community session with Nayibeej and grassroots non-profit partners.', date:'05 Dec 2026' }
  ],
  press: [
    { tag:'Press release', title:'VOTESS announces Non-Profit ERP platform', text:'Dedicated enterprise suite engineered exclusively for NGOs and social trusts enters development.', date:'06 Oct 2026' },
    { tag:'Press release', title:'VOTESS announces its group structure', text:'VOTESS will operate commercial brands alongside its support for the independent non-profit Nayibeej.', date:'03 Oct 2026' },
    { tag:'Press release', title:'Nexus ERP announced as flagship education product', text:'A single platform for school administration, teaching and parent engagement.', date:'25 Sep 2026' },
    { tag:'Press release', title:'VOTESS establishes cloud infrastructure operations', text:'Centralized multi-tenant hosting delivering high reliability and data compliance.', date:'15 Sep 2026' }
  ]
};
