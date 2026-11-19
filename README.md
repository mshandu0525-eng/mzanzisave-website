# MzanziSave website

Company website with Tailwind CSS from the CDN. There is no build step.

- `index.html`: the main page (services, approach, value, contact form via Netlify Forms)
- `app.html`: MzanziSave V2 showcase, with the live app embedded and an interactive final-price builder
- `assets/`: images

## Deploy on Netlify
1. Netlify → **Add new site → Import an existing project**, then pick this repo. Leave the build command empty and the publish directory as `.`.
2. **Forms → Enable form detection**, then redeploy.
3. **Site configuration → Notifications → Form submission notifications** to get inquiries by email.
