<!DOCTYPE html>
<html lang="en">
<head>
    <title>Phil Stephens - Resume</title>
    <meta charset="UTF-8">
    <meta name="author" content="Phil Stephens">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
    :root {
        --white: 214 20% 98%;
        --light-gray: 214 33% 94%;
        --medium-gray: 214 33% 86%;
        --dark: 214 33% 10%;
        --pink:345 80% 60%
    }

    html {
        background: #ebeff5;
        background: hsl(var(--light-gray));
        color: #111822;
        color: hsl(var(--dark));
        margin: 0;
        padding: 0;
        -webkit-font-smoothing: antialiased;
        font-size:14px
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
        width: 1.2em
    }

    body {
        background: #f9fafb;
        background: hsl(var(--white));
        border-radius: .25rem;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif;
        line-height: 1.5rem;
        margin: 1rem auto;
        max-width: min(100%, 80ch);
        padding: .5rem 1.5rem;
        position:relative;
        font-size: 1.1rem;
    }

    h1 {
        font-size: 1.6rem;
        padding: 0 3rem;
        text-align:center
    }

    h2 {
        font-size: 1.2rem;
        font-weight: 900;
        margin-bottom: 1rem;
        margin-top: 3rem;
        text-transform:uppercase
    }

    h3 {
        font-size: 1rem;
        margin-bottom: .25rem;
        position:relative
    }

    h3, h4 {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-top:0
    }

    h4 {
        font-style: italic;
        font-weight: 400;
        margin-bottom:.5rem
    }

    h4 a {
        display: inherit;
        justify-content: inherit;
        width:100%
    }

    p, ul {
        margin-bottom: 1.5rem;
        margin-top:0
    }

    ol {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        list-style: none;
        margin: 0 0 1rem;
        padding:0
    }

    ol > li {
        background: #111822;
        background: hsl(var(--dark));
        border: 1px solid #111822;
        border: 1px solid hsl(var(--dark));
        border-radius: .25rem;
        color: #f9fafb;
        color: hsl(var(--white));
        font-size: .9rem;
        font-weight: 700;
        line-height: 1.25rem;
        padding:.2rem .3rem
    }

    section.company {
        margin-bottom:3rem
    }

    header {
        margin-bottom:1.5rem
    }

    .spread {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        margin:0 auto .5rem
    }

    .spread span {
        word-wrap:normal
    }

    footer {
        font-size: .8rem;
        text-align:right
    }

    a {
        color: #111822;
        text-decoration-color: #111822;
        text-decoration-style: solid;
        text-decoration-thickness:2px
    }

    a:focus, a:hover {
        text-decoration-color: #111822;
    }

    h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {
        -webkit-text-decoration: none;
        text-decoration:none
    }

    .print-btn {
        /* background: #eb47700d; */
        background: hsl(var(--dark) /.05);
        /* border: 1px solid hsl(345 80% 60%/.3); */
        border: 1px solid hsl(var(--dark) /.3);
        border-radius: .25rem;
        cursor: pointer;
        font-size: 1rem;
        padding: .5rem;
        position: absolute;
        right: 1.5rem;
        top:1.5rem
    }

    .print {
        display:none
    }

    @media print {
        body, html {
            background: #fff;
            color: #000;
            margin: 0;
            max-width: 100%;
            padding:0
        }

        .print {
            display:inherit
        }

        .noprint {
            display:none
        }

        h1 {
            margin-bottom: 1rem;
            margin-top:0
        }

        h2, h3, h4 {
            color: #000;
            page-break-after: avoid;
            -moz-column-break-after: avoid;
            break-after:avoid-page
        }

        a {
            color: #000;
            text-decoration-color:#000
        }

        .break {
            page-break-after: always;
            -moz-column-break-after: page;
            break-after:page
        }

        .print-link:after {
            content: " (" attr(href) ")"
        }

        ol > li {
            background: #f9fafb;
            background: hsl(var(--white));
            border: 1px solid #111822;
            border: 1px solid hsl(var(--dark));
            color: #111822;
            color: hsl(var(--dark))
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
            <a class="noprint" href="mailto:hello@philstephens.com">Email</a>
            <a class="noprint" href="https://philstephens.com">Website</a>
            <a class="noprint" href="https://github.com/theprivateer">Code</a>
            <span class="print">Email: hello@philstephens.com</span>
            <span class="print">Web: philstephens.com</span>
            <span class="print">Code: github.com/theprivateer</span>
        </div>
    </header>
    <p>
        <em>From SaaS platforms to high-traffic websites, I have been making things for the web for more than 20 years. I foster healthy teams with an empathetic leadership style and bring equal amounts of technical proficiency, leadership expertise, and business acumen. I strongly believe in simple, pragmatic, and maintainable solutions; shipping small things frequently; and getting fast feedback.</em>
    </p>
    <h2>Career Highlights</h2>

    <p>Full career history including details of contract roles can be provided on request.</p>

    <section class="company">
        <h3>
            <a href="https://rexsoftware.com">Rex</a>
            <em>Brisbane, Australia</em>
        </h3>
        <h4>
            Technical Lead
            <em>2021 - 2025</em>
        </h4>
        <ul>
            <li>Stepped in as Technical Lead for the Rex Sites (previously Siteloft) product at a critical point, resetting expectations, stabilising delivery, and rebuilding a team that had been struggling to meet business needs.</li>
            <li>Took a pragmatic approach to product and engineering leadership across a complex legacy WordPress platform and a stalled next-generation stack, focusing on progress and reliability over unnecessary technical complexity.</li>
            <li>Played a key role in finally shipping the new platform and expanding it into the ANZ and UK markets, balancing technical decisions with commercial realities.</li>
            <li>Later assumed end-to-end ownership of both Rex Sites and Rex Reach (previously Spoke - a digital marketing automation platform) as sole technical and product lead, working closely with sales, support, clients, and partners across regions to keep both platforms growing and operational.</li>
        </ul>
    </section>
    <section class="company">
        <h3>
            <a href="https://databee.com.au">Databee Business Systems</a>
            <em>Perth, Australia (Remote)</em>
        </h3>
        <h4>
            Technical Director and Senior Developer
            <em>2017 - 2021</em>
        </h4>
        <ul>
            <li>Set up stronger development and release practices, brought in clearer specs, and improved support workflows so the team could focus more on product work.</li>
            <li>Led the company's first AWS-based deployment for a major Sydney university, drawing on deep cloud experience.</li>
            <li>Helped shape a healthy remote culture across four states and introduced better knowledge sharing through Confluence.</li>
            <li>Modernised key PHP web components and later moved into a hands-on development role, mentoring junior staff and guiding new product work using solid engineering practices.</li>
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
            <li>Stabilised a fragile legacy platform and began breaking it into modern, service-based components using Laravel and current engineering standards.</li>
            <li>Shifted the business to a cloud-first model on AWS, cutting infrastructure costs and adopting a lean DevOps approach.</li>
            <li>Built and mentored an onshore technical team, introduced Scrum, strengthened security practices, and improved roadmap planning with founders and the board.</li>
            <li>Delivered key innovations including a cross-platform mobile app, major third-party integrations, and R&D initiatives, while staying hands-on across the codebase.</li>
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
            <li>Designed and delivered a bespoke digital platform that met a key client commitment and became the foundation of the agency's hotel and travel solution.</li>
            <li>Overhauled the technical offering with a cloud-first approach on AWS and DigitalOcean, while securing partnerships with key third-party providers.</li>
            <li>Expanded and strengthened the development team, giving the agency the capability to take on larger and more complex work.</li>
            <li>Introduced a clear delivery pipeline and contributed to major tenders, helping win high-value projects with national and international hotel groups.</li>
        </ul>
    </section>

    <section class="company">
        <h3>
            <a href="https://macbackpackers.com">MacBackpackers</a>
            <em>Edinburgh, Scotland</em>
        </h3>
        <h4>
            Digital and Technical Manager
            <em>2008 - 2014</em>
        </h4>
        <ul>
            <li>Led technical strategy and built a full end-to-end booking system while maintaining all core infrastructure and digital operations.</li>
            <li>Created global marketing collateral and expanded the reseller network across the UK, Australia and Canada, representing the company at industry events.</li>
            <li>Launched a member benefits platform later adopted by the Royal Edinburgh Military Tattoo, boosting brand visibility at no extra cost.</li>
            <li>Supported operations directly by managing a hostel for six months to inform product design and improve performance, and successfully worked remotely for a year across New Zealand and Australia.</li>
        </ul>
    </section>

    <section class="company">
        <h3>
            Ripe Design International
            <em>Leeds, UK</em>
        </h3>
        <h4>
            Creative Technologist
            <em>2007 - 2008</em>
        </h4>
        <ul>
            <li>Built rich media applications using Flex, ActionScript, and PHP.</li>
            <li>Researched new online technologies and developed interactive web applications.</li>
            <li>Designed, built, and maintained website products for international clients.</li>
            <li>Presented to stakeholders on-site nationally and internationally and contributed to creative pitches.</li>
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
    <h2>Skills</h2>
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
        <li>AWS</li>
        <li>GCP</li>
        <li>Microsoft Server / IIS</li>
        <li>REST APIs</li>
        <li>Event-Driven Architecture</li>
        <li>Distributed Systems</li>
        <li>Modular Monoliths</li>
        <li>Legacy Modernisation</li>
    </ol>
    <h3>Leadership</a></h3>
    <ol>
        <li>Fostering high-performing teams</li>
        <li>Building a sustainable engineering culture</li>
        <li>Mentoring</li>
        <li>Coaching</li>
        <li>Agile software development</li>
        <li>Roadmap planning</li>
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
    <footer>
        Last update: 12 January 2026
    </footer>
</body>
</html>
