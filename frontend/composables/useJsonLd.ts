import { computed, isRef, unref } from 'vue'

export const useJsonLd = () => {
  const addJsonLd = (schema: Record<string, any> | any) => {
    useHead({
      script: [
        {
          type: 'application/ld+json',
          innerHTML: computed(() => JSON.stringify(unref(schema), null, 2)),
        },
      ],
    })
  }

  const organizationSchema = () => {
    addJsonLd({
      '@context': 'https://schema.org',
      '@type': 'Organization',
      name: 'Lamjaya Global Solusi',
      url: 'https://lamsolusi.com',
      logo: 'https://lamsolusi.com/images/logo.png',
      contactPoint: {
        '@type': 'ContactPoint',
        telephone: '+62881082203778',
        contactType: 'Customer Service',
        email: 'info@lamsolusi.com',
        areaServed: 'ID',
        availableLanguage: ['Indonesian', 'English']
      },
      address: {
        '@type': 'PostalAddress',
        streetAddress: 'Graha Mustika Ratu 4th Floor, Jalan Gatot Subroto Kav 74-75',
        addressLocality: 'Jakarta',
        addressRegion: 'DKI Jakarta',
        postalCode: '12870',
        addressCountry: 'ID'
      }
    })
  }

  const websiteSchema = () => {
    addJsonLd({
      '@context': 'https://schema.org',
      '@type': 'WebSite',
      name: 'Lamjaya Global Solusi',
      url: 'https://lamsolusi.com',
      description: 'SAP Integration & App Development',
      publisher: {
        '@type': 'Organization',
        name: 'Lamjaya Global Solusi'
      }
    })
  }

  return {
    addJsonLd,
    organizationSchema,
    websiteSchema
  }
}
