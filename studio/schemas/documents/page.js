export default {
  name: 'page',
  title: 'Page',
  type: 'document',
  fields: [
    {
      name: 'title',
      title: 'Title',
      type: 'string'
    },
    {
      name: 'slug',
      title: 'Slug',
      type: 'slug',
      options: {
        source: 'title',
        maxLength: 96
      }
    },
    {
      name: 'mainImage',
      title: 'Main image',
      type: 'image',
      options: {
        hotspot: true
      }
    },
    {
      name: 'excerpt',
      type: 'excerptPortableText',
      title: 'Excerpt',
      description:
        'Wird als Überschrift in der ersten Sektion auf der Hauptseite der Page angezeigt'
    },
    {
      name: 'body',
      type: 'bodyPortableText',
      title: 'Body'
    },
    {
      title: "Sections",
      name: "sections",
      type: 'array',
      of: [
        {
          type: "section",
        }
      ],
      options:{
        // sortable: false,
         //editModal: 'popover'
        // layout: 'grid'
      }
    },
    {
      name: 'infotitle',
      title: 'Info title',
      type: 'string'
    },
    {
      name: 'infoheadline',
      title: 'Info headline',
      type: 'excerptPortableText'
    },
    {
      title: "Info boxes",
      name: "infoboxes",
      type: 'array',
      of: [
        {
          type: "infobox",
        }
      ],
      options:{
        // sortable: false,
         //editModal: 'popover'
        // layout: 'grid'
      }
    },
    {
      name: 'social_twitter',
      title: 'Twitter',
      type: 'string'
    },
    {
      name: 'social_facebook',
      title: 'Facebook',
      type: 'string'
    },
    {
      name: 'social_insta',
      title: 'Instagram',
      type: 'string'
    },
    {
      name: 'social_email',
      title: 'Email',
      type: 'string'
    },
    {
      title: "Footer",
      name: "footer",
      type: 'array',
      of: [
        {
          type: "section",
        }
      ],
      options:{
        // sortable: false,
         //editModal: 'popover'
        // layout: 'grid'
      }
    }
  ]
}
