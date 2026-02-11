<!DOCTYPE html>
<html lang="en">
<head>
    <title>Phil Stephens - Resume</title>
    <meta charset="UTF-8">
    <meta name="author" content="Phil Stephens">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ url('img/favicon.png') }}">
    <style>
    :root {
        --white: 214 20% 98%;
        --grey: 214 33% 94%;
        --dark: 214 33% 10%;
    }

    html {
        background: hsl(var(--grey));
        color: hsl(var(--dark));
        margin: 0;
        padding: 0;
        -webkit-font-smoothing: antialiased;
        font-size: 14px;
    }

    .icon {
        display: inline-block;
        fill: none;
        height: 1.2em;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.5;
        vertical-align: text-bottom;
        width: 1.2em;
    }

    body {
        background: hsl(var(--white));
        border-radius: .25rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        line-height: 1.5rem;
        margin: 1rem auto;
        max-width: min(100%, 80ch);
        padding: .5rem 1.5rem;
        position: relative;
        font-size: 1.1rem;
    }

    h1 {
        font-size: 1.6rem;
        padding: 0 3rem;
        text-align: center;
    }

    h2 {
        font-size: 1.2rem;
        font-weight: 900;
        margin-bottom: 1rem;
        margin-top: 3rem;
        text-transform: uppercase;
    }

    h3 {
        font-size: 1rem;
        margin-bottom: .25rem;
        position: relative;
    }

    h3, h4 {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-top: 0;
    }

    h4 {
        font-style: italic;
        font-weight: 400;
        margin-bottom: .5rem;
    }

    h4 a {
        display: inherit;
        justify-content: inherit;
        width: 100%;
    }

    p, ul {
        margin-bottom: 1.5rem;
        margin-top: 0;
    }

    ol {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        list-style: none;
        margin: 0 0 1rem;
        padding: 0;
    }

    ol > li {
        background: hsl(var(--dark));
        border: 1px solid hsl(var(--dark));
        border-radius: .25rem;
        color: hsl(var(--white));
        font-size: .9rem;
        font-weight: 700;
        line-height: 1.25rem;
        padding: .2rem .3rem;
    }

    section.company {
        margin-bottom: 3rem;
    }

    header {
        margin-bottom: 1.5rem;
    }

    .spread {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        margin: 0 auto .5rem;
    }

    .spread span {
        word-wrap: normal;
    }

    footer {
        padding-top: 1rem;
        font-size: .8rem;
    }

    a {
        color: hsl(var(--dark));
        text-decoration-color: hsl(var(--dark));
        text-decoration-style: solid;
        text-decoration-thickness: 2px;
    }

    a:focus, a:hover {
        text-decoration-color: hsl(var(--dark));
    }

    h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {
        -webkit-text-decoration: none;
        text-decoration: none;
    }

    .print-btn {
        background: hsl(var(--dark) /.05);
        border: 1px solid hsl(var(--dark) /.3);
        border-radius: .25rem;
        cursor: pointer;
        font-size: 1rem;
        padding: .5rem;
        position: absolute;
        right: 1.5rem;
        top: 1.5rem;
    }

    .print {
        display: none;
    }

    @media print {
        body, html {
            background: hsl(var(--white));
            color: hsl(var(--dark));
            margin: 0;
            max-width: 100%;
            padding: 1rem;
            font-size: .88rem;
        }

        .print {
            display: inherit;
        }

        .noprint {
            display: none;
        }

        h1 {
            margin-bottom: 1rem;
            margin-top: 0;
        }

        h2, h3, h4 {
            color: hsl(var(--dark));
            page-break-after: avoid;
            -moz-column-break-after: avoid;
            break-after: avoid-page;
        }

        a {
            color: hsl(var(--dark));
            text-decoration-color: hsl(var(--dark));
        }

        .print-link:after {
            content: " (" attr(href) ")";
        }

        ol > li {
            background: hsl(var(--white));
            border: 1px solid hsl(var(--dark));
            color: hsl(var(--dark));
            font-weight: 400;
            font-size: .75rem;
        }

        .pullout {
            padding: 1rem;
            border: 1px solid hsl(var(--dark));
            border-radius: .25rem;
        }
    }
    </style>
</head>
<body>
    <button onclick="window.print();" class="print-btn noprint">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
        </svg>
    </button>
    <header>
        <h1>Phil Stephens</h1>
        <div class="spread">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                 Brisbane, Australia
            </span>
        </div>
        <div class="spread">
            <a class="noprint" href="mailto:hello@philstephens.com">Email</a>
            <a class="noprint" href="https://philstephens.com">Website</a>
            <a class="noprint" href="https://github.com/theprivateer">Code</a>
            <span class="print"><strong>Email:</strong>&nbsp;hello@philstephens.com</span>
            @if(request()->has('phone'))
            <span class="print"><strong>Phone:</strong>&nbsp;{{ config('app.contact_phone_number') }}</span>
            @endif
            <span class="print"><strong>Web:</strong>&nbsp;philstephens.com</span>
        </div>
    </header>
    <p class="pullout">
        I've been working with technology and the web for over twenty years, moving from architecture and design into software development and engineering leadership. Along the way, I've helped teams build and implement products and systems that are practical, maintainable, and grounded in real-world use and business outcomes. I combine
technical pragmatism and commercial acumen with a touch of creative flair.
    </p>

    <h2>Skills</h2>
    <h3>Leadership</a></h3>
    <ol>
        <li>Fostering high-performing teams</li>
        <li>Building a sustainable engineering culture</li>
        <li>Mentoring</li>
        <li>Coaching</li>
        <li>Agile software development</li>
        <li>Roadmap planning</li>
        <li>Systems implementation</li>
        <li>Vendor management</li>
        <li>Digital transformation</li>
    </ol>
    <h3>Domains</h3>
    <ol>
        <li>E-Commerce</li>
        <li>EdTech</li>
        <li>Real Estate</li>
        <li>Tourism</li>
        <li>SaaS</li>
        <li>B2B Software</li>
        <li>Accessibility</li>
    </ol>
    <h3>Software Engineering</h3>
    <ol>
        <li>PHP
        <li>Laravel</li>
        <li>Node.js</li>
        <li>Ruby</li>
        <li>Claris FileMaker</li>
        <li>JavaScript</li>
        <li>React</li>
        <li>Vue.js</li>
        <li>Tailwind CSS</li>
        <li>Alpine.js</li>
        <li>Continuous Delivery (CI/CD)</li>
        <li>Test-Driven Development</li>
        <li>Elasticsearch</li>
        <li>Redis</li>
        <li>MySQL</li>
        <li>PostgreSQL</li>
        <li>Kubernetes</li>
        <li>AWS</li>
        <li>GCP</li>
        <li>Microsoft Server / IIS</li>
        <li>REST APIs</li>
        <li>Event-Driven Architecture</li>
        <li>Distributed Systems</li>
        <li>Modular Monoliths</li>
        <li>Legacy Modernisation</li>
    </ol>

    <h2>Career Highlights</h2>

    <p>Full career history including details of contract roles can be provided on request.</p>

    <section class="company">
        <h3>
            <a href="https://rexsoftware.com">Rex Software</a>
            <em>Brisbane, Australia</em>
        </h3>
        <h4>
            Technical Lead
            <em>2021 - 2025</em>
        </h4>
        <ul>
            <li>Brought in to stabilise and reset two underperforming product teams, rebuilding delivery capability by re-establishing trust with the business, setting clear expectations, and growing a new engineering team alongside product leadership.</li>
            <li>Took ownership of two complex, multi-market SaaS platforms, leading the successful release of a stalled next-generation product into ANZ and UK markets while maintaining and evolving a large-scale legacy platform supporting 600+ customer websites.</li>
            <li>Held end-to-end responsibility for technical strategy, delivery planning, operational reliability, and incident management, while working closely with senior stakeholders and high-value customers to ensure products delivered measurable business value.</li>
            <li>During organisational change, assumed full technical and product ownership for both platforms, working directly with sales, support, customers, and third-party partners across regions to ensure continued growth, stability, and commercial alignment.</li>
        </ul>
    </section>
    <section class="company">
        <h3>
            <a href="https://databee.com.au">Databee Business Systems</a>
            <em>Perth, Australia (Remote)</em>
        </h3>
        <h4>
            Technical Director
            <em>2017 - 2021</em>
        </h4>
        <ul>
            <li>Acted as senior technology partner to the founder, shaping technology strategy, delivery approach, and operational maturity so the business could reliably support enterprise customers in a highly regulated environment.</li>
            <li>Raised organisational engineering capability by introducing clearer governance around development, release, and support processes, improving predictability, reducing operational risk, and enabling sustained product investment.</li>
            <li>Led critical technology initiatives including the company's first cloud-based deployment on AWS for a major enterprise client, embedding strong security and data-handling practices for sensitive personal and regulated data.</li>
            <li>Built and sustained a remote-first engineering culture across a fully distributed team, establishing knowledge management, mentoring pathways, and technical standards that allowed the organisation to scale beyond founder-led delivery.</li>
        </ul>
    </section>

    <section class="company">
        <h3>
            <a href="https://iseekplant.com.au">iSeekplant</a>
            <em>Brisbane, Australia</em>
        </h3>
        <h4>
            Development Lead
            <em>2016 - 2017</em>
        </h4>
        <ul>
            <li>Took ownership of a fragile, third-party built legacy platform, stabilising delivery and leading a multi-year modernisation strategy that transitioned the system toward a service-oriented architecture aligned with long-term business objectives.</li>
            <li>Led a cloud-first transformation, migrating the organisation from costly dedicated infrastructure to AWS and introducing a lean operational model that improved reliability, scalability, and delivery confidence while reducing overhead.</li>
            <li>Partnered closely with founders and board members to define and deliver a technology roadmap aligned to short, medium, and long-term business goals; built, managed, and mentored an onshore engineering team while coordinating delivery across offshore developers.</li>
            <li>Established sustainable engineering practices across the organisation, including agile delivery, backlog ownership, security standards, and knowledge sharing, enabling faster innovation, successful enterprise integrations, and R&amp;D tax incentive claims.</li>
        </ul>
    </section>

    <section class="company">
        <h3>
            Fastrack Group
            <em>Brisbane, Australia</em>
        </h3>
        <h4>
            Technical and Delivery Lead
            <em>2015 - 2016</em>
        </h4>
        <ul>
            <li>Delivered a critical client commitment by architecting and launching a bespoke digital platform that became the foundation of the agency's long-term solution for hotel and travel providers.</li>
            <li>Repositioned the agency's technical capability through a cloud-first strategy, strategic third-party partnerships, and modernisation of its digital offering, strengthening its ability to compete for larger contracts.</li>
            <li>Built and scaled the development team while introducing a structured delivery pipeline, improving cross-department collaboration, execution consistency, and success in national and international tenders.</li>
        </ul>
    </section>

    <section class="company">
        <h3>
            <span><a href="https://macbackpackers.com">MacBackpackers</a> / <a href="https://scotlandstophostels.com">Scotland's Top Hostels</a></span>
            <em>Edinburgh, Scotland</em>
        </h3>
        <h4>
            Digital and Technical Manager
            <em>2008 - 2014</em>
        </h4>
        <ul>
            <li>Owned the company's end-to-end digital and technology strategy, working closely with the founder and senior leadership to align technology, operations, and commercial objectives across a multi-site hospitality business.</li>
            <li>Led the design and delivery of a custom booking and management platform supporting coach tours and backpacker hostels across eight locations, while overseeing company-wide infrastructure, hardware, and software modernisation.</li>
            <li>Delivered operational improvements by leading critical vendor and platform migrations, including payment systems and email infrastructure, improving reliability, cost control, and day-to-day efficiency.</li>
            <li>Embedded technology into the broader business through international reseller partnerships, a member benefits platform later adopted by a major cultural institution, and hands-on operational leadership to inform system design and improve on-site performance.</li>
        </ul>
    </section>

    <h2>Education</h2>
    <h4>
        Bachelor of Architecture, University of Sheffield
        <em>1997 - 2000</em>
    </h4>
    <h4>
        Master of Architecture, University of Sheffield
        <em>2001 - 2003</em>
    </h4>
    <h4>
        BCS Foundation Certificate in Agile, The Chartered Institute for IT
        <em>2018</em>
    </h4>
    <footer class="spread">
        <span>Last update: 11 February 2026</span>
        <span>philstephens.com/resume</span>
    </footer>
</body>
</html>
